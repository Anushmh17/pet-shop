"""
=============================================================
 core/detector.py — YOLOv8 Pet Detection Engine
=============================================================
"""

import cv2
import logging
from ultralytics import YOLO
from core.config import MODEL_PATH, CONFIDENCE_THRESHOLD, IOU_THRESHOLD, MAX_IMAGE_SIZE, PET_CLASS_MAP

logger = logging.getLogger(__name__)


class PetDetector:
    """
    Wraps a YOLOv8 model. Call run(image_path) to get detections.
    The model is loaded once at server startup to keep requests fast.
    """

    def __init__(self):
        # Determine if we're using the custom brain or base brain 
        is_custom = "best.pt" in str(MODEL_PATH)
        brain_type = "🧠 CUSTOM BRAIN (Smarter)" if is_custom else "🥚 BASE BRAIN (Standard)"
        
        print(f"-----------------------------------")
        print(f"  LOADING {brain_type}")
        print(f"  Path: {MODEL_PATH}")
        print(f"-----------------------------------")
        
        # Load model into memory
        self.model = YOLO(str(MODEL_PATH))

    # ── Public ────────────────────────────────────────────────────────────────

    def run(self, image_path: str) -> dict:
        """
        Full inference pipeline.

        Returns:
            {
              "total_animals": int,
              "animals": {"cat": 2, "dog": 1},
              "detections": [{"label": "cat", "confidence": 0.92, "bbox": [x,y,w,h]}]
            }
        """
        image = self._load_image(image_path)
        raw   = self._infer(image)
        return self._parse(raw)

    # ── Private ───────────────────────────────────────────────────────────────

    def _load_image(self, path: str):
        """Load image and downscale if too large to keep inference fast."""
        img = cv2.imread(path)
        if img is None:
            raise ValueError(f"Cannot open image: {path}")

        h, w = img.shape[:2]
        if max(h, w) > MAX_IMAGE_SIZE:
            scale = MAX_IMAGE_SIZE / max(h, w)
            img = cv2.resize(img, (int(w * scale), int(h * scale)), interpolation=cv2.INTER_AREA)

        return img

    def _infer(self, image):
        """Run YOLO inference. Returns a list of Results objects."""
        return self.model.predict(
            source=image,
            conf=CONFIDENCE_THRESHOLD,
            iou=IOU_THRESHOLD,
            verbose=False,
        )

    def _parse(self, raw_results) -> dict:
        """
        Filter raw detections to only pet-shop animals.
        Convert bbox from [x1,y1,x2,y2] → [x, y, width, height].
        """
        animals:    dict[str, int] = {}
        detections: list[dict]     = []

        is_custom = "best.pt" in str(self.model.ckpt_path if hasattr(self.model, 'ckpt_path') else self.model.pt_path)
        
        for box in raw_results[0].boxes:
            class_id   = int(box.cls[0])
            
            # Use the model's internal names dictionary directly!
            # If it's a base model, we filter for dog/cat/bird.
            # If it's the custom model, all its internal names are valid user-defined animals.
            label = self.model.names.get(class_id, "unknown")
            
            # Filter if base model
            if not is_custom and label not in ["dog", "cat", "bird", "rabbit"]:
                continue
                
            confidence = float(box.conf[0])
            x1, y1, x2, y2 = [round(v) for v in box.xyxy[0].tolist()]

            animals[label] = animals.get(label, 0) + 1
            detections.append({
                "label":      label,
                "confidence": round(confidence, 3),
                "bbox":       [x1, y1, x2 - x1, y2 - y1],   # x, y, w, h
            })

        return {
            "total_animals": sum(animals.values()),
            "animals":       animals,
            "detections":    detections,
        }
