from pathlib import Path

from ultralytics import YOLO


class FoodDetector:
    def __init__(self, model_path: str, conf: float = 0.5):
        self.model_path = Path(model_path)
        self.conf = conf
        self.model = None
        self._class_names: dict[int, str] = {}

        if self.model_path.exists():
            self.model = YOLO(str(self.model_path))
            self._class_names = dict(self.model.names)

    @property
    def is_loaded(self) -> bool:
        return self.model is not None

    @property
    def class_names(self) -> list[str]:
        return list(self._class_names.values())

    def detect_best(self, image_path: str) -> dict:
        self._ensure_loaded()

        results = self.model.predict(
            source=image_path,
            conf=self.conf,
            save=False,
            verbose=False,
        )

        best_confidence = 0.0
        best_name = None

        for result in results:
            if result.boxes is None:
                continue

            for box in result.boxes:
                confidence = float(box.conf[0])
                if confidence > best_confidence:
                    class_id = int(box.cls[0])
                    best_confidence = confidence
                    best_name = self._class_names.get(class_id, str(class_id))

        if best_name is None:
            return {"food_name": None, "confidence": 0}

        return {
            "food_name": best_name,
            "confidence": round(best_confidence, 4),
        }

    def _ensure_loaded(self) -> None:
        if not self.is_loaded:
            raise FileNotFoundError(
                f"Model not found at {self.model_path}. Train first with: python train.py"
            )
