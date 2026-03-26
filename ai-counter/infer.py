"""
=============================================================
 infer.py — Command-line inference script (quick test tool)
=============================================================
 Usage:
   python infer.py --image path/to/photo.jpg
   python infer.py --image photo.jpg --show   # display annotated image

 This is useful for quick local testing without starting the server.
=============================================================
"""

import argparse
import json
import sys
import cv2
import numpy as np
from pathlib import Path

# Ensure project root is in sys.path
sys.path.insert(0, str(Path(__file__).parent))

from core.detector import PetDetector
from core.config import ALLOWED_LABELS


def draw_boxes(image_path: str, detections: list) -> None:
    """
    Draw coloured bounding boxes on the image and display it.
    Each animal type gets a unique colour.
    """
    img = cv2.imread(image_path)
    if img is None:
        print(f"Cannot load image for display: {image_path}")
        return

    # Colour palette per species
    COLORS = {
        "cat":    (255, 165,   0),    # Orange
        "dog":    (0,   200, 100),    # Green
        "bird":   (0,   120, 255),    # Blue
        "fish":   (200,   0, 200),    # Purple
        "rabbit": (0,   200, 200),    # Cyan
    }
    default_color = (180, 180, 180)

    for det in detections:
        label      = det["label"]
        confidence = det["confidence"]
        x, y, w, h = det["bbox"]

        color = COLORS.get(label, default_color)
        x2, y2 = x + w, y + h

        # Draw rectangle
        cv2.rectangle(img, (x, y), (x2, y2), color, 2)

        # Draw label background + text
        text  = f"{label} {confidence:.0%}"
        (tw, th), _ = cv2.getTextSize(text, cv2.FONT_HERSHEY_SIMPLEX, 0.55, 1)
        cv2.rectangle(img, (x, y - th - 8), (x + tw + 6, y), color, -1)
        cv2.putText(img, text, (x + 3, y - 5),
                    cv2.FONT_HERSHEY_SIMPLEX, 0.55, (255, 255, 255), 1)

    cv2.imshow("Pet Detector — press any key to close", img)
    cv2.waitKey(0)
    cv2.destroyAllWindows()


def main():
    parser = argparse.ArgumentParser(description="Pet detector CLI")
    parser.add_argument("--image", required=True, help="Path to JPEG/PNG image")
    parser.add_argument("--show",  action="store_true",
                        help="Display annotated image in a window")
    args = parser.parse_args()

    image_path = args.image
    if not Path(image_path).exists():
        print(f"Error: File not found: {image_path}")
        sys.exit(1)

    print(f"\n🐾 Running inference on: {image_path}")
    detector = PetDetector()
    result = detector.run(image_path)

    # Pretty-print JSON result
    print("\n─── Detection Result ───────────────────────────")
    print(json.dumps(result, indent=2))
    print(f"\n✅ Total animals detected: {result['total_animals']}")
    for species, count in result["animals"].items():
        print(f"   {species:<12}: {count}")

    if args.show:
        draw_boxes(image_path, result["detections"])


if __name__ == "__main__":
    main()
