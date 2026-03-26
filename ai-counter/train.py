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

    # 1. Get unique labels from feedback
    labels = set()
    feedback_files = list(FEEDBACK_DIR.glob("*.json"))
    for f in feedback_files:
        with f.open() as j:
            data = json.load(j)
            labels.add(data.get("correction", "animal").lower())
    
    label_map = {name: i for i, name in enumerate(sorted(list(labels)))}
    
    # 2. Use teacher model to find boxes (Pseudo-labeling)
    teacher = YOLO(str(MODELS_DIR / "yolov8n.pt"))
    
    count = 0
    for f in feedback_files:
        fb_id = f.stem
        img_path = FEEDBACK_DIR / f"{fb_id}.jpg"
        if not img_path.exists(): continue
        
        with f.open() as j:
            fb_data = json.load(j)
            correct_label = fb_data.get("correction", "animal").lower()
            class_id = label_map[correct_label]

        # Run teacher to get box coordinates
        results = teacher(str(img_path), verbose=False)
        
        # We save the boxes found by the teacher but with the NEW label
        label_txt = DATASET_DIR / "labels" / f"{fb_id}.txt"
        with label_txt.open("w") as lt:
            found_any = False
            for r in results:
                for box in r.boxes:
                    # YOLO format: class x_center y_center width height (normalized)
                    xywhn = box.xywhn[0].tolist()
                    lt.write(f"{class_id} {' '.join(map(str, xywhn))}\n")
                    found_any = True
            
            # If teacher found nothing, we assume the whole center of the image is the object
            if not found_any:
                lt.write(f"{class_id} 0.5 0.5 0.8 0.8\n")

        # Copy image to dataset
        shutil.copy(img_path, DATASET_DIR / "images" / f"{fb_id}.jpg")
        count += 1

    # 3. Create data.yaml
    data_yaml = {
        "path": str(DATASET_DIR.absolute()),
        "train": "images",
        "val": "images", # We use same for small datasets
        "names": {v: k for k, v in label_map.items()}
    }
    
    yaml_path = BASE_DIR / "auto_data.yaml"
    with yaml_path.open("w") as yf:
        yaml.dump(data_yaml, yf)
    
    return yaml_path, count

def train_new_model():
    """Main entry point for training."""
    print("\n🐾 Starting Pet Shop AI Evolution...")
    print("─────────────────────────────────────────────────────────────")
    
    try:
        # 1. Build dataset
        yaml_path, count = generate_auto_dataset()
        if count == 0:
            print("[ERROR] No images found in feedback/ folder.")
            return

        # 2. Load model
        model = YOLO(str(MODELS_DIR / "yolov8n.pt"))

        # 3. Train
        # Small epochs (10-20) for quick feedback loop
        model.train(
            data=str(yaml_path),
            epochs=20,
            imgsz=640,
            project=str(BASE_DIR / "runs"),
            name="evolution",
            exist_ok=True,
            verbose=True
        )

        # 4. Deploy results (Copy best weights to models)
        best_pt = BASE_DIR / "runs" / "evolution" / "weights" / "best.pt"
        if best_pt.exists():
            shutil.copy(best_pt, MODELS_DIR / "best.pt")
            print(f"\n✅ SUCCESS! New brain 'best.pt' created from {count} feedback images.")
        else:
            print("\n[ERROR] Training finished but could not find weights.")

    except Exception as e:
        print(f"\n[FATAL ERROR] {str(e)}")
        raise e

if __name__ == "__main__":
    train_new_model()
