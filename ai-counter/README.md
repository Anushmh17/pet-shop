# AI Pet Counting System 🐾

This module adds a mobile-first AI animal detection and counting system to the Pet Shop Management System. Users can capture photos with their phone cameras or upload from the gallery to instantly count pets.

## 🚀 Getting Started

### 1. Prerequisites
- **Python 3.9+** installed on the server/local machine.
- **PHP/XAMPP** environment (already exists for the main project).

### 2. Setup Python Environment
Open a terminal in the `ai-counter` directory and run:

```bash
# Create a virtual environment
python -m venv venv

# Activate it
# On Windows:
venv\Scripts\activate
# On Linux/macOS:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt
```

### 3. Run the AI Backend
Start the FastAPI server:

```bash
python -m uvicorn main:app --host 0.0.0.0 --port 8000 --reload
```
> [!TIP]
> Use `--host 0.0.0.0` to allow access from your mobile phone on the same Wi-Fi network.

### 4. Access the Frontend
1. Open your browser to `http://localhost/petshop/pages/index.php`.
2. Click the new **AI Counter** card.
3. If on mobile, you can use `http://<YOUR_PC_IP>/petshop/pages/index.php`.

---

## 🛠️ How to Fine-Tune (Custom Models)

The system currently uses the pre-trained **YOLOv8 nano** model. To support animals like **Fish** or **Rabbits** (which are not in the standard COCO dataset), follow these steps:

### 1. Collect Data
Take 100-200 photos of fish tanks and rabbit cages from different angles and lighting.

### 2. Annotate (YOLO Format)
Use tools like [Roboflow](https://roboflow.com/) or [LabelImg](https://github.com/HumanSignal/labelImg) to draw bounding boxes around each animal.
Export in **YOLO format** (a folder of images and a folder of `.txt` files).

### 3. Train the Model
You can use Google Colab or your local machine with a GPU:

```python
from ultralytics import YOLO

# Load a base model
model = YOLO('yolov8n.pt')

# Train for 50-100 epochs
model.train(data='your_dataset.yaml', epochs=100, imgsz=640)
```

### 4. Deploy Custom Model
1. Copy the resulting `best.pt` file to `ai-counter/models/`.
2. Update `ai-counter/core/config.py`:
   - Set `MODEL_PATH` to point to `best.pt`.
   - Update `PET_CLASS_MAP` with your new class IDs (e.g., `0: "fish", 1: "rabbit"`).

---

## 📁 Folder Structure
- `api/`: FastAPI routes and request handling.
- `core/`: YOLOv8 inference engine and configuration.
- `models/`: Storage for `.pt` weight files.
- `pages/ai-counter.php`: The mobile-first frontend integration.
- `main.py`: Backend entry point.
- `infer.py`: CLI tool for quick testing.

---

## 🔒 Privacy & Performance
- **No Storage:** Images are written to a temporary folder and deleted immediately after analysis.
- **Latency:** Inference takes ~100-300ms on a standard CPU and ~20ms on a GPU.
- **Mobile First:** The UI uses native camera APIs and is optimized for small touch screens.
