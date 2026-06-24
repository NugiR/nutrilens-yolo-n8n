from flask import Flask
from flask_cors import CORS

from api.config import CONF_THRESHOLD, FLASK_CORS_ORIGINS, MODEL_PATH
from api.detector import FoodDetector
from api.routes import bp


def create_app() -> Flask:
    app = Flask(__name__)
    CORS(app, origins=FLASK_CORS_ORIGINS)

    detector = FoodDetector(model_path=MODEL_PATH, conf=CONF_THRESHOLD)
    app.config["DETECTOR"] = detector

    app.register_blueprint(bp)
    return app
