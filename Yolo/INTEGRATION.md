# Flask YOLO API — Integration Guide

Flask handles **object detection only**. Nutrition lookup is handled by n8n via Laravel.

## Architecture

```text
Browser (camera) → Flask /api/detect/frame → food_name + confidence
Browser → Laravel POST /api/meal-logs → n8n (food_name only) → Laravel webhook → nutrition display
```

## Run

```bash
cd Yolo
venv\Scripts\activate
pip install -r requirements.txt
copy .env.example .env
python run.py
```

Default URL: `http://localhost:5000`

## Endpoints

### GET /health

```json
{ "status": "ok", "model_loaded": true, "classes": 21 }
```

### GET /api/classes

Returns all 21 food class names from the trained model.

### POST /api/detect/frame

**Input:** `multipart/form-data`, field `frame` (JPEG/PNG blob from canvas)

**Output:**

```json
{ "food_name": "Rendang", "confidence": 0.87 }
```

No detection above threshold:

```json
{ "food_name": null, "confidence": 0 }
```

## Browser integration

Configure Laravel `.env`:

```env
VITE_YOLO_API_URL=http://localhost:5000
FLASK_CORS_ORIGINS=http://localhost:8000
```

Example loop (throttle ~300–500ms):

```javascript
const YOLO_API = import.meta.env.VITE_YOLO_API_URL;

async function detectFrame(blob) {
  const form = new FormData();
  form.append('frame', blob, 'frame.jpg');
  const res = await fetch(`${YOLO_API}/api/detect/frame`, { method: 'POST', body: form });
  return res.json(); // { food_name, confidence }
}
```

Submit stable detection to Laravel:

```javascript
await fetch('/api/meal-logs', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    meal_type: 'pagi',
    food_name: 'Rendang',
    confidence: 0.87,
  }),
});
```

## n8n contract (via Laravel)

Laravel sends to n8n (no image):

```json
{
  "meal_log_id": 1,
  "food_name": "Rendang",
  "confidence": 0.87,
  "meal_type": "pagi",
  "date": "2026-06-24",
  "user_id": 1
}
```

n8n returns nutrition to Laravel:

```http
POST http://localhost:8000/api/webhook/nutrition-result
X-Webhook-Secret: your_shared_secret
```

```json
{
  "meal_log_id": 1,
  "food_name": "Rendang",
  "calories": 320,
  "protein": 25.0,
  "carbs": 5.0,
  "fat": 22.0,
  "fiber": 1.5,
  "vitamins": { "vitamin_a": 50 },
  "summary": "Rendang daging sapi."
}
```
