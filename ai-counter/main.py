"""
=============================================================
 PET SHOP AI COUNTER — FastAPI Backend
 main.py — Application entry point
=============================================================
 Run with:
   uvicorn main:app --host 0.0.0.0 --port 8000 --reload
=============================================================
"""

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from api.routes import router

# Initialize FastAPI app
app = FastAPI(
    title="Pet Shop AI Counter",
    description="Detect and count animals in images using YOLOv8.",
    version="1.0.0",
)

# ─── CORS Middleware ───────────────────────────────────────────────────────────
# Allow all origins so the mobile frontend (any device on LAN) can reach the API.
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],      # Tighten in production (e.g. your domain only)
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ─── Register API routes ───────────────────────────────────────────────────────
app.include_router(router)


@app.get("/")
async def root():
    """Health-check endpoint."""
    return {"status": "ok", "service": "Pet Shop AI Counter v1.0"}
