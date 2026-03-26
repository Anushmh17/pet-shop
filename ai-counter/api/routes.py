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
from fastapi import APIRouter, UploadFile, File, HTTPException
from fastapi.responses import JSONResponse

from core.detector import PetDetector

# ─── New: Feedback directory (for future training) ───────────────────────────
FEEDBACK_DIR = Path(__file__).parent.parent / "feedback"
FEEDBACK_DIR.mkdir(exist_ok=True)

class FeedbackRequest(BaseModel):
    image_data: str   # base64 encoded image
    label: str        # user correction (e.g. "Goldfish")

# ─── Router ───────────────────────────────────────────────────────────────────
router = APIRouter()

@router.post("/submit-correction")
async def submit_correction(req: FeedbackRequest):
    """
    Saves the image (base64) and the user's corrected label
    into the 'feedback/' folder for human-in-the-loop learning.
    """
    try:
        # Generate unique ID (first part of UUID)
        fb_id = str(uuid.uuid4()).split('-')[0]
        
        # Decode image (format: "data:image/jpeg;base64,xxxx")
        header, encoded = req.image_data.split(",", 1)
        image_bytes = base64.b64decode(encoded)
        
        # Save image
        img_path = FEEDBACK_DIR / f"{fb_id}.jpg"
        img_path.write_bytes(image_bytes)
        
        # Save label metadata
        meta_path = FEEDBACK_DIR / f"{fb_id}.json"
        with meta_path.open("w") as f:
            json.dump({"id": fb_id, "correction": req.label}, f)
            
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
