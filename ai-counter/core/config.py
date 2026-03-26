"""
=============================================================
 core/config.py — Central configuration for the AI engine
=============================================================
"""

from pathlib import Path

# ─── Model path ───────────────────────────────────────────────
# yolov8n.pt is auto-downloaded on first run.
# After fine-tuning, replace with: Path(__file__).parent.parent / "models" / "best.pt"
MODEL_PATH = Path(__file__).parent.parent / "models" / "yolov8n.pt"

# ─── Inference thresholds ─────────────────────────────────────
CONFIDENCE_THRESHOLD = 0.40   # min 40% confidence
IOU_THRESHOLD        = 0.45   # NMS threshold

# ─── Image size cap ───────────────────────────────────────────
MAX_IMAGE_SIZE = 1280         # pixels (longest edge)

# ─── COCO Class IDs that map to pet-shop animals ─────────────
#
# Standard COCO pre-trained IDs:
#   14 = bird | 15 = cat | 16 = dog
#
# NOTE: "fish" and "rabbit" are NOT in the default COCO dataset.
# They will work correctly only after you fine-tune the model.
# See FINE_TUNING_GUIDE.md for step-by-step instructions.
#
# After fine-tuning, add your custom class IDs here, e.g.:
#   88: "fish"
#   89: "rabbit"
#
PET_CLASS_MAP: dict[int, str] = {
    14: "bird",
    15: "cat",
    16: "dog",
    # 88: "fish",       ← uncomment after fine-tuning
    # 89: "rabbit",     ← uncomment after fine-tuning
}
