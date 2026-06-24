import tempfile
from pathlib import Path

from flask import Blueprint, current_app, jsonify, request

from api.detector import FoodDetector

bp = Blueprint("api", __name__)


def _detector() -> FoodDetector:
    return current_app.config["DETECTOR"]


@bp.get("/health")
def health():
    detector = _detector()
    return jsonify(
        {
            "status": "ok",
            "model_loaded": detector.is_loaded,
            "classes": len(detector.class_names),
        }
    )


@bp.get("/api/classes")
def classes():
    detector = _detector()

    if not detector.is_loaded:
        return jsonify({"success": False, "error": "Model not loaded"}), 503

    return jsonify(
        {
            "success": True,
            "classes": [
                {"id": idx, "name": name}
                for idx, name in enumerate(detector.class_names)
            ],
        }
    )


@bp.post("/api/detect/frame")
def detect_frame():
    detector = _detector()

    if not detector.is_loaded:
        return jsonify(
            {
                "food_name": None,
                "confidence": 0,
                "error": "Model not loaded. Train first with: python train.py",
            }
        ), 503

    if "frame" not in request.files:
        return jsonify(
            {
                "food_name": None,
                "confidence": 0,
                "error": "Multipart field 'frame' is required",
            }
        ), 400

    frame_file = request.files["frame"]
    if not frame_file.filename and not frame_file.content_length:
        return jsonify(
            {
                "food_name": None,
                "confidence": 0,
                "error": "Empty frame upload",
            }
        ), 400

    suffix = Path(frame_file.filename or "frame.jpg").suffix or ".jpg"

    try:
        with tempfile.NamedTemporaryFile(suffix=suffix, delete=False) as tmp:
            frame_file.save(tmp.name)
            tmp_path = tmp.name

        try:
            result = detector.detect_best(tmp_path)
        finally:
            Path(tmp_path).unlink(missing_ok=True)

        return jsonify(result)

    except FileNotFoundError as exc:
        return jsonify({"food_name": None, "confidence": 0, "error": str(exc)}), 503
    except Exception as exc:
        return jsonify({"food_name": None, "confidence": 0, "error": str(exc)}), 500
