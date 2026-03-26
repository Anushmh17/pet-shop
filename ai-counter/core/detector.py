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
        logger.info(f"Loading YOLOv8 model from: {MODEL_PATH}")
        # ultralytics auto-downloads yolov8n.pt on first use if the file doesn't exist
        self.model = YOLO(str(MODEL_PATH))
        logger.info("✅ Model loaded successfully.")

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

        for box in raw_results[0].boxes:
            class_id   = int(box.cls[0])
            label      = PET_CLASS_MAP.get(class_id)
            if label is None:
                continue        # skip non-pet objects

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
