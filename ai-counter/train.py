"""
=============================================================
 train.py — AI Training / Fine-Tuning Script
=============================================================
 Usage:
   1. Collect images from the 'feedback/' folder.
   2. Annotate them (YOLO format) using Roboflow or LabelImg.
   3. Place your dataset.yaml and data into 'ai-counter/dataset/'.
   4. Run this script: python train.py
=============================================================
"""

import os
import sys
from ultralytics import YOLO
from pathlib import Path

def train_new_model():
    print("\n🐾 Starting Pet Shop AI Training...")
    print("─────────────────────────────────────────────────────────────")

    # 1. Load the base model (Start from pre-trained weights)
    # yolov8n.pt is the fastest and best for mobile phones.
    model_path = Path("models/yolov8n.pt")
    if not model_path.exists():
        print(f"[!] Downloading base weights to {model_path}...")
    
    model = YOLO(str(model_path))

    # 2. Configure Dataset
    # You MUST create 'data.yaml' first after annotating your photos.
    # See README.md for instructions on how to use Roboflow.
    dataset_config = "data.yaml"
    
    if not os.path.exists(dataset_config):
        print(f"\n[ERROR] '{dataset_config}' not found!")
        print("Please place your annotated YOLO dataset and data.yaml in this folder.")
        print("See README.md for the step-by-step guide.")
        return

    # 3. Train the model
    # epochs=50 is standard for a small dataset (100-500 images).
    # imgsz=640 is standard high-resolution for YOLOv8.
    print(f"[STEP] Re-training the brain using '{dataset_config}'...")
    results = model.train(
        data=dataset_config,
        epochs=50,
        imgsz=640,
        plots=True,
        cache=True,
        verbose=True
    )

    print("\n✅ TRAINING COMPLETE!")
    print("─────────────────────────────────────────────────────────────")
    print(f"Your new brain is saved in: runs/detect/train/weights/best.pt")
    print("To USE it, copy 'best.pt' into 'models/' and update 'core/config.py'.")

if __name__ == "__main__":
    train_new_model()
