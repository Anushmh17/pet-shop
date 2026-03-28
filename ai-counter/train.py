"""
=============================================================
 train.py — AI Auto-Trainer (Human-in-the-Loop)
=============================================================
"""

import os
import json
import shutil
import yaml
import time
from pathlib import Path
from ultralytics import YOLO

# ─── Configuration ────────────────────────────────────────────
BASE_DIR = Path(__file__).parent
FEEDBACK_DIR = BASE_DIR / "feedback"
DATASET_DIR = BASE_DIR / "auto_dataset"
MODELS_DIR = BASE_DIR / "models"
STATUS_FILE = BASE_DIR / "training_status.json"

# ─── MASTER CATEGORY LIST (Order must never change!) ──────────
MASTER_CLASSES = ["bird", "cat", "dog", "fish", "rabbit"]

def update_status(status, progress=0, message="", classes=[]):
    """Write current progress to a file for the website to read."""
    with open(STATUS_FILE, "w") as f:
        json.dump({
            "status": status,      # 'idle', 'studying', 'success', 'error'
            "progress": progress,  # 0 to 100
            "message": message,
            "classes": classes,
            "timestamp": time.time()
        }, f)

def generate_auto_dataset():
    """Converts feedback into YOLO format."""
    update_status("studying", 5, "📦 Preparing images...")
    
    if DATASET_DIR.exists(): shutil.rmtree(DATASET_DIR)
    (DATASET_DIR / "images").mkdir(parents=True)
    (DATASET_DIR / "labels").mkdir(parents=True)

    # 1. Fixed Class Vocabulary
    # We use a STABLE list so the AI doesn't mix up Birds and Fish!
    items = list(FEEDBACK_DIR.glob("*.json"))
    label_map = {name: i for i, name in enumerate(MASTER_CLASSES)}
    all_classes = list(MASTER_CLASSES) # Create a copy
    
    # Check if user has any new custom labels beyond the master list
    for f in items:
        with f.open() as j:
            try: 
                lbl = json.load(j).get("correction", "fish").lower()
                if lbl not in label_map:
                    # Append new unknown animal to the master list
                    label_map[lbl] = len(all_classes)
                    all_classes.append(lbl)
            except: continue
    
    # 2. Extract boxes
    teacher = YOLO(str(MODELS_DIR / "yolov8n.pt"))
    count = 0
    for i, f in enumerate(items):
        fb_id = f.stem
        img_path = FEEDBACK_DIR / f"{fb_id}.jpg"
        if not img_path.exists(): continue
        
        with f.open() as j:
            try: 
                fb_data = json.load(j)
                class_id = label_map[fb_data.get("correction", "fish").lower()]
                user_boxes = fb_data.get("boxes") # list of [x,y,w,h]
            except: continue

        label_txt = DATASET_DIR / "labels" / f"{fb_id}.txt"
        found_any = False
        
        # 🤖 TEACHER HELPER (To see if other animals are in plain sight)
        results = teacher(str(img_path), conf=0.15, verbose=False)
        
        with label_txt.open("w") as lt:
            if user_boxes and len(user_boxes) > 0:
                # 🚀 USER DRAWN BOXES (Priority for the corrected animal)
                for b in user_boxes:
                    if len(b) == 4:
                        lt.write(f"{class_id} {' '.join(map(str, b))}\n")
                        found_any = True
            
            # 🧠 SMART TEACHER: Look for other pets we know about to prevent confusion
            # (e.g. if there's a bird in the background of a fish photo, label it as a bird)
            for r in results:
                for box in r.boxes:
                    t_cid = int(box.cls[0])
                    # COCO Map: 14:bird, 15:cat, 16:dog
                    t_name = {14:"bird", 15:"cat", 16:"dog"}.get(t_cid)
                    
                    if t_name and t_name in label_map:
                        t_target_cid = label_map[t_name]
                        # Don't overlap with user's specific correction if they are the same area
                        lt.write(f"{t_target_cid} {' '.join(map(str, box.xywhn[0].tolist()))}\n")
                        found_any = True
                    elif not user_boxes:
                        # If user didn't draw, and it's not a COCO pet, use the correction for the FIRST found object
                        lt.write(f"{class_id} {' '.join(map(str, box.xywhn[0].tolist()))}\n")
                        found_any = True
        
        if not found_any:
            if label_txt.exists(): os.remove(label_txt)
        else:
            shutil.copy(img_path, DATASET_DIR / "images" / f"{fb_id}.jpg")
            count += 1
        
        # Incremental progress (up to 20%)
        p = 5 + int((i+1)/len(items) * 15)
        update_status("studying", p, f"📦 Processing image {i+1} of {len(items)}...")

    # 3. data.yaml
    data_yaml = {
        "path": str(DATASET_DIR.absolute()),
        "train": "images", "val": "images",
        "names": {v: k for k, v in label_map.items()}
    }
    with open(BASE_DIR / "auto_data.yaml", "w") as yf: yaml.dump(data_yaml, yf)
    
    return count, label_map

def train_new_model():
    print("\n🐾 Evolution Mode Activated...")
    try:
        update_status("studying", 0, "🚀 Starting Engine...")
        
        count, lmap = generate_auto_dataset()
        if count == 0:
            update_status("error", 0, "❌ No clear images found to learn from.")
            return

        # 🚀 THE STUDY SESSION
        total_epochs = 30 # Restored to 30 for maximum learning accuracy

        # Determine starting checkpoint (Latest Brain or Fresh Start from Standard)
        best_ckpt = MODELS_DIR / "best.pt"
        start_from_fresh = True
        
        if best_ckpt.exists():
            # 🔥 SAFETY CHECK: Verify that the current brain uses the correct slot mapping
            # (e.g. if an old model thinks 'Fish' is slot 0, but the new code thinks 'Bird' is slot 0, then we MUST reset)
            try:
                temp_model = YOLO(str(best_ckpt))
                # Check if the labels match our MASTER_CLASSES exactly
                if getattr(temp_model, 'names') and list(temp_model.names.values())[:5] == MASTER_CLASSES:
                    print(f"📦 Slot-Mapping Verified. Continuing study for: {list(temp_model.names.values())}")
                    start_from_fresh = False
                else:
                    print(f"⚠️ Brain Conflict! Mapping changed. Wiping old memory and starting fresh to fix confusion.")
            except:
                print(f"⚠️ Could not verify brain. Starting fresh.")

        if not start_from_fresh:
            print(f"📦 Fine-tuning from existing best model: {best_ckpt}")
            model = YOLO(str(best_ckpt))
        else:
            print(f"📦 Starting fresh from base model: {MODELS_DIR / 'yolov8n.pt'}")
            model = YOLO(str(MODELS_DIR / 'yolov8n.pt'))

        def on_train_epoch_end(trainer):
            """Callback to update progress bar on website."""
            cur = trainer.epoch + 1
            progress = 20 + int((cur / total_epochs) * 75)
            update_status("studying", progress, f"🧠 Studying... epoch {cur}/{total_epochs}", list(lmap.keys()))

        model.add_callback("on_train_epoch_end", on_train_epoch_end)

        model.train(
            data=str(BASE_DIR / "auto_data.yaml"),
            epochs=total_epochs, imgsz=640, batch=4, device="cpu",
            project=str(BASE_DIR / "runs"), name="evolution", exist_ok=True, verbose=False,
            resume=False # Never resume; always start fresh fine-tuning on new data
        )

        # 4. Success
        best_pt = BASE_DIR / "runs" / "evolution" / "weights" / "best.pt"
        if best_pt.exists():
            shutil.copy(best_pt, MODELS_DIR / "best.pt")
            update_status("success", 100, f"✅ Brain Updated with {count} photos!", list(lmap.keys()))
        else:
            update_status("error", 0, "❌ Could not finalize weights.")

    except Exception as e:
        update_status("error", 0, f"💥 Error: {str(e)}")
        raise e

if __name__ == "__main__":
    train_new_model()
