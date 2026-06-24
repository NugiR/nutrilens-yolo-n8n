"""Download Indonesia Food dataset from Roboflow."""
from roboflow import Roboflow

rf = Roboflow(api_key="")
project = rf.workspace("yolo-obopn").project("indonesia-food-tjvly")
version = project.version(1)
dataset = version.download("yolov11")

print(f"Dataset downloaded to: {dataset.location}")
