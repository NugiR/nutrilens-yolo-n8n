import os
from pathlib import Path

from dotenv import load_dotenv

YOLO_ROOT = Path(__file__).resolve().parent.parent
load_dotenv(YOLO_ROOT / ".env")

MODEL_PATH = os.getenv(
    "MODEL_PATH",
    str(YOLO_ROOT / "runs" / "detect" / "indonesia-food-v2" / "weights" / "best.pt"),
)
CONF_THRESHOLD = float(os.getenv("CONF_THRESHOLD", "0.5"))
FLASK_HOST = os.getenv("FLASK_HOST", "0.0.0.0")
FLASK_PORT = int(os.getenv("FLASK_PORT", "5000"))
FLASK_DEBUG = os.getenv("FLASK_DEBUG", "false").lower() in ("1", "true", "yes")
FLASK_CORS_ORIGINS = [
    origin.strip()
    for origin in os.getenv("FLASK_CORS_ORIGINS", "http://localhost:8000,http://localhost:8001,http://127.0.0.1:8000,http://127.0.0.1:8001").split(",")
    if origin.strip()
]
