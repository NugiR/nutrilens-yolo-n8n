"""Train YOLO11s on Indonesia Food dataset with optimized augmentation."""
import torch
from ultralytics import YOLO

DATA_YAML = "Indonesia-Food-1/data.yaml"
MODEL = "yolo11s.pt"
PROJECT_NAME = "indonesia-food-v2"

device = 0 if torch.cuda.is_available() else "cpu"
batch = 16 if torch.cuda.is_available() else 4

print(f"Device: {device}")
print(f"Batch size: {batch}")
print(f"Model: {MODEL}")

model = YOLO(MODEL)
model.train(
    data=DATA_YAML,
    epochs=150,
    imgsz=640,
    batch=batch,
    device=device,
    name=PROJECT_NAME,
    patience=30,
    plots=True,
    # Augmentation
    hsv_h=0.015,
    hsv_s=0.7,
    hsv_v=0.4,
    degrees=10,
    translate=0.1,
    scale=0.5,
    fliplr=0.5,
    mosaic=1.0,
    # Learning rate schedule
    lr0=0.01,
    warmup_epochs=5,
    cos_lr=True,
)
