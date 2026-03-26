"""
=============================================================
 train.py — AI Auto-Trainer (Human-in-the-Loop)
=============================================================
 This script automatically converts your 'feedback/' folder
 into a valid YOLO dataset and trains a new model.
=============================================================
"""

import os
import json
import shutil
import yaml
import cv2
from pathlib import Path
from ultralytics import YOLO

# ─── Configuration ────────────────────────────────────────────
BASE_DIR = Path(__file__).parent
FEEDBACK_DIR = BASE_DIR / "feedback"
DATASET_DIR = BASE_DIR / "auto_dataset"
MODELS_DIR = BASE_DIR / "models"

def generate_auto_dataset():
    """Converts feedback JSON + JPG into YOLO format."""
    print("📦 Building auto-dataset from feedback...")
    
    # Clean old dataset
    if DATASET_DIR.exists():
        shutil.rmtree(DATASET_DIR)
    
    (DATASET_DIR / "images").mkdir(parents=True)
    (DATASET_DIR / "labels").mkdir(parents=True)

    # 1. Get unique labels
    labels = set()
    feedback_files = list(FEEDBACK_DIR.glob("*.json"))
    for f in feedback_files:
        with f.open() as j:
            try:
                data = json.load(j)
                labels.add(data.get("correction", "fish").lower())
            except: continue
    
    if not labels: labels.add("fish") # fallback
    label_map = {name: i for i, name in enumerate(sorted(list(labels)))}
    print(f"  Mapped classes: {label_map}")

    # 2. Teacher model (Very sensitive!)
    teacher = YOLO(str(MODELS_DIR / "yolov8n.pt"))
    
    count = 0
    for f in feedback_files:
        fb_id = f.stem
        img_path = FEEDBACK_DIR / f"{fb_id}.jpg"
        if not img_path.exists(): continue
        
        with f.open() as j:
            try:
                fb_data = json.load(j)
                correct_label = fb_data.get("correction", "fish").lower()
                class_id = label_map[correct_label]
            except: continue

        # Run teacher with VERY LOW confidence (0.05) to find ANYTHING box-shaped
        results = teacher(str(img_path), conf=0.05, verbose=False)
        
        # Save boxes
        label_txt = DATASET_DIR / "labels" / f"{fb_id}.txt"
        found_any = False
        with label_txt.open("w") as lt:
            for r in results:
                for box in r.boxes:
                    xywhn = box.xywhn[0].tolist()
                    lt.write(f"{class_id} {' '.join(map(str, xywhn))}\n")
                    found_any = True
        
        # ⚠️ NO MORE GUESSING! 
        # If teacher found nothing at 0.05 confidence, we skip this image
        # because bad boxes = bad AI brain.
        if not found_any:
            if label_txt.exists(): os.remove(label_txt)
            print(f"  ⚠️ Skipping {fb_id}.jpg (Teacher could not find boxes).")
            continue

        # Copy image to dataset
        shutil.copy(img_path, DATASET_DIR / "images" / f"{fb_id}.jpg")
        count += 1

    # 3. Create data.yaml
    data_yaml = {
        "path": str(DATASET_DIR.absolute()),
        "train": "images",
        "val": "images",
        "names": {v: k for k, v in label_map.items()}
    }
    
    yaml_path = BASE_DIR / "auto_data.yaml"
    with yaml_path.open("w") as yf:
        yaml.dump(data_yaml, yf)
    
    return yaml_path, count, label_map

def train_new_model():
    """Main entry point for training."""
    print("\n🐾 Starting Pet Shop AI Evolution...")
    print("─────────────────────────────────────────────────────────────")
    
    try:
        # 1. Build dataset
        yaml_path, count, label_map = generate_auto_dataset()
        if count == 0:
            print("[ERROR] No valid data found. Try more clear photos!")
            return

        # 2. Load base model
        model = YOLO(str(MODELS_DIR / "yolov8n.pt"))

        # 3. Train
        # More epochs (50) and smaller batch for better learning on small data
        model.train(
            data=str(yaml_path),
            epochs=50,
            imgsz=640,
            batch=4,
            project=str(BASE_DIR / "runs"),
            name="evolution",
            exist_ok=True,
            verbose=False
        )

        # 4. Deploy results
        best_pt = BASE_DIR / "runs" / "evolution" / "weights" / "best.pt"
        if best_pt.exists():
            shutil.copy(best_pt, MODELS_DIR / "best.pt")
            print(f"\n✅ SUCCESS! New brain 'best.pt' created.")
            print(f"   Processed {count} images with classes: {label_map}")
        else:
            print("\n[ERROR] Training finished but could not find weights.")

    except Exception as e:
        print(f"\n[FATAL ERROR] {str(e)}")
        raise e

if __name__ == "__main__":
    train_new_model()
