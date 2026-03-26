"""
=============================================================
 core/config.py — Central configuration for the AI engine
=============================================================
"""

from pathlib import Path

# ─── Model Selection ──────────────────────────────────────────
# We prefer our custom-trained 'best.pt' brain if it exists.
# Otherwise, we fall back to the base 'yolov8n.pt' brain.
MODELS_DIR = Path(__file__).parent.parent / "models"
MODEL_PATH = MODELS_DIR / "best.pt" if (MODELS_DIR / "best.pt").exists() else MODELS_DIR / "yolov8n.pt"

# ─── Inference thresholds ─────────────────────────────────────
CONFIDENCE_THRESHOLD = 0.15   # Lowered to 15% to catch newly learned animals!
IOU_THRESHOLD        = 0.45   # NMS threshold

# ─── Image size cap ───────────────────────────────────────────
MAX_IMAGE_SIZE = 1280         # pixels (longest edge)

# ─── Class Mapping ────────────────────────────────────────────
# Pre-trained COCO classes + New custom classes.
# Note: When training from scratch, labels begin at 0.
PET_CLASS_MAP: dict[int, str] = {
    0: "fish",      # Our newly trained class 
    1: "rabbit",    # Secondary trained class
    14: "bird",     # COCO legacy
    15: "cat",      # COCO legacy
    16: "dog",      # COCO legacy
}
