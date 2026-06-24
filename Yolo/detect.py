"""Run inference with trained Indonesia Food YOLO11 model."""
import argparse
from pathlib import Path

from ultralytics import YOLO

DEFAULT_MODEL = "runs/detect/indonesia-food/weights/best.pt"
DEFAULT_SOURCE = "Indonesia-Food-1/test/images"


def main():
    parser = argparse.ArgumentParser(description="Detect Indonesian food in images/video/webcam")
    parser.add_argument("--model", default=DEFAULT_MODEL, help="Path to trained weights (.pt)")
    parser.add_argument("--source", default=DEFAULT_SOURCE, help="Image, folder, video path, or 0 for webcam")
    parser.add_argument("--conf", type=float, default=0.5, help="Confidence threshold")
    parser.add_argument("--show", action="store_true", help="Show results in a window")
    args = parser.parse_args()

    if not Path(args.model).exists():
        raise FileNotFoundError(
            f"Model not found: {args.model}\n"
            "Train first with: python train.py"
        )

    model = YOLO(args.model)
    results = model.predict(
        source=args.source,
        save=True,
        conf=args.conf,
        show=args.show,
    )

    print(f"Done. {len(results)} image(s) processed.")
    print("Results saved to runs/detect/predict/")


if __name__ == "__main__":
    main()
