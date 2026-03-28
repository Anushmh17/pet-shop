"""
=============================================================
 api/routes.py — FastAPI route definitions
=============================================================
 Defines the POST /detect-and-count endpoint.
 Handles image upload, delegates inference to the AI engine,
 and returns a clean JSON response.
=============================================================
"""

import tempfile
import os
import json
import uuid
import base64
from pathlib import Path
from pydantic import BaseModel
from fastapi import APIRouter, UploadFile, File, HTTPException, BackgroundTasks
from fastapi.responses import JSONResponse

from core.detector import PetDetector

# ─── Training Status State ──────────────────────────────────────────────────
# Keep track of when the AI is 'studying' so we don't start twice.
_is_training = False
_last_training_result = "Never trained"

def run_training_task():
    """Background task to run the actual training logic."""
    global _is_training, _last_training_result
    try:
        # Import train script logic here
        from train import train_new_model
        # NOTE: In a real production environment, you'd use a subprocess
        # or a worker (Celery/RQ) so it doesn't slow down the main API.
        # This is a 'simplified' implementation for the demo.
        train_new_model()
        _last_training_result = "Success (Updated!)"
    except Exception as e:
        _last_training_result = f"Failed: {str(e)}"
    finally:
        _is_training = False


# ─── New: Feedback directory (for future training) ───────────────────────────
FEEDBACK_DIR = Path(__file__).parent.parent / "feedback"
FEEDBACK_DIR.mkdir(exist_ok=True)

class FeedbackRequest(BaseModel):
    image_data: str   # base64 encoded image
    label: str        # user correction (e.g. "Goldfish")
    boxes: list[list[float]] | None = None  # optional [[x,y,w,h], ...] normalized 0-1

# ─── Router ───────────────────────────────────────────────────────────────────
router = APIRouter()

@router.post("/train/trigger")
async def trigger_training(background_tasks: BackgroundTasks):
    """Start the 'Self-Learning' process in the background."""
    global _is_training, _last_training_result
    
    # 1. Check if we have enough data (at least 5 images recommended)
    feedback_count = len(list(FEEDBACK_DIR.glob("*.jpg")))
    if feedback_count < 1:
        raise HTTPException(status_code=400, detail="Not enough feedback data! Try correcting an image first.")

    # 2. Prevent concurrent training
    if _is_training:
        return {"status": "busy", "message": "The AI is already studying!"}

    # 3. Queue the task
    _is_training = True
    _last_training_result = "Studying..."
    background_tasks.add_task(run_training_task)
    
    return {"status": "started", "message": "AI Evolution started in the background!"}


@router.get("/train/status")
async def get_training_status():
    """Get the current state of the 'Self-Learning' process."""
    status_file = Path(__file__).parent.parent / "training_status.json"
    
    if not status_file.exists():
        return {"status": "idle", "progress": 0, "message": "Ready to learn!"}

    try:
        with open(status_file, "r") as f:
            return json.load(f)
    except:
        return {"status": "idle", "progress": 0, "message": "Ready to learn!"}


@router.post("/submit-correction")
async def submit_correction(req: FeedbackRequest):
    """
    Saves the image (base64) and the user's corrected label
    into the 'feedback/' folder for human-in-the-loop learning.
    """
    try:
        # 1. Extract image bytes correctly
        if "," in req.image_data:
            _, encoded = req.image_data.split(",", 1)
        else:
            encoded = req.image_data
        image_bytes = base64.b64decode(encoded)
        
        # 2. Content-Aware Hashing (Prevents duplicates even if metadata varies)
        import hashlib
        from PIL import Image
        import io
        
        # We hash the raw RGB pixels to ensure only the image content matters
        try:
            with Image.open(io.BytesIO(image_bytes)) as img:
                pixels = img.convert("RGB").tobytes()
                fb_id = hashlib.md5(pixels).hexdigest()[:8]
        except:
            # Fallback to byte hash if image can't be decoded
            fb_id = hashlib.md5(image_bytes).hexdigest()[:8]
        
        # 3. Save Image (Overwrite allowed)
        img_path = FEEDBACK_DIR / f"{fb_id}.jpg"
        img_path.write_bytes(image_bytes)
        
        # Save label metadata
        meta_path = FEEDBACK_DIR / f"{fb_id}.json"
        with meta_path.open("w") as f:
            json.dump({
                "id": fb_id,
                "correction": req.label,
                "boxes": req.boxes or []  # List of [x,y,w,h]
            }, f)
            
        return {"status": "success", "message": "Feedback saved! We'll use this to teach the AI."}
    
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# ─── Singleton detector (model loaded once at startup) ────────────────────────
# Loading YOLOv8 is expensive (~1-2 s). We keep one instance alive for the
# lifetime of the server so subsequent requests are fast (< 200 ms on CPU).
_detector: PetDetector | None = None


def get_detector() -> PetDetector:
    global _detector
    if _detector is None:
        _detector = PetDetector()   # loads model on first call
    return _detector


# ─── Supported MIME types ─────────────────────────────────────────────────────
ALLOWED_CONTENT_TYPES = {"image/jpeg", "image/png", "image/webp"}


@router.post(
    "/detect-and-count",
    summary="Detect and count animals in an image",
    response_description="JSON with total count, per-species breakdown, and bounding boxes",
)
async def detect_and_count(image: UploadFile = File(...)):
    """
    **POST /detect-and-count**

    Upload a JPEG / PNG image (phone camera or gallery).

    Returns:
    - `total_animals` – integer total across all species
    - `animals` – dict mapping species name → count
    - `detections` – list of every bounding box with label + confidence
    """

    # ── 1. Validate file type ──────────────────────────────────────────────────
    if image.content_type not in ALLOWED_CONTENT_TYPES:
        raise HTTPException(
            status_code=415,
            detail=f"Unsupported file type: {image.content_type}. Use JPEG or PNG.",
        )

    # ── 2. Write upload to a temporary file (never persisted after request) ───
    suffix = ".jpg" if "jpeg" in (image.content_type or "") else ".png"
    tmp_path: str = ""
    try:
        # Use mode 'wb' explicitly for writing bytes
        with tempfile.NamedTemporaryFile(delete=False, suffix=suffix, mode='wb') as tmp:
            contents = await image.read()
            tmp.write(contents)
            tmp_path = tmp.name

        # ── 3. Run AI inference ───────────────────────────────────────────────
        detector = get_detector()
        result = detector.run(tmp_path)

    except Exception as exc:
        raise HTTPException(status_code=500, detail=f"Inference error: {str(exc)}")

    finally:
        # ── 4. Delete temp file — images are NEVER stored ─────────────────────
        if tmp_path and os.path.exists(tmp_path):
            os.remove(tmp_path)

    return JSONResponse(content=result)
