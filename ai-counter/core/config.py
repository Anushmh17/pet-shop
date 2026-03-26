"""
=============================================================
 core/config.py — Central configuration for the AI engine
=============================================================
"""

import json
from pathlib import Path

# ─── Model Selection ──────────────────────────────────────────
BASE_DIR = Path(__file__).parent.parent
MODELS_DIR = BASE_DIR / "models"
STATUS_FILE = BASE_DIR / "training_status.json"

# We prefer our custom-trained 'best.pt' brain if it exists.
# Otherwise, we fall back to the base 'yolov8n.pt' brain.
MODEL_PATH = MODELS_DIR / "best.pt" if (MODELS_DIR / "best.pt").exists() else MODELS_DIR / "yolov8n.pt"

# ─── Inference thresholds ─────────────────────────────────────
CONFIDENCE_THRESHOLD = 0.20   # Balanced for both base and custom models
IOU_THRESHOLD        = 0.45   # NMS threshold

# ─── Image size cap ───────────────────────────────────────────
MAX_IMAGE_SIZE = 1280         # pixels (longest edge)

# ─── Dynamic Class Mapping ─────────────────────────────────────
# We try to read the class names from the last training session.
def get_class_map():
    base_map = {14: "bird", 15: "cat", 16: "dog"}
    
    if STATUS_FILE.exists():
        try:
            with open(STATUS_FILE, "r") as f:
                data = json.load(f)
                if "classes" in data and data["classes"]:
                    # Create a map for the custom indices (0, 1, 2...)
                    custom_map = {i: name for i, name in enumerate(data["classes"])}
                    # Merge with base map
                    return {**base_map, **custom_map}
        except: pass
    
    # Fallback to defaults
    return {**base_map, 0: "fish", 1: "rabbit"}

PET_CLASS_MAP = get_class_map()
