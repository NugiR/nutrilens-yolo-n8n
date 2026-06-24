# NutriLens — Kalkulator Nutrisi Berbasis Kamera & YOLO

Monorepo aplikasi **NutriLens**: deteksi makanan Indonesia dengan **YOLO11** (Flask) dan analisis nutrisi via **Laravel** + **n8n**.

User scan makanan lewat kamera browser → YOLO mendeteksi jenis makanan → Laravel meneruskan kategori ke n8n → n8n menghitung nutrisi → hasil ditampilkan di dashboard harian.

---

## Arsitektur

```text
┌─────────────┐     frame JPEG      ┌──────────────┐
│   Browser   │ ──────────────────► │  Flask YOLO  │
│  (kamera)   │ ◄── food_name + ─── │  port 5000   │
└──────┬──────┘     confidence      └──────────────┘
       │
       │ POST meal-logs (food_name, confidence)
       ▼
┌──────────────┐   webhook (food_name)   ┌─────────┐
│    Laravel   │ ───────────────────────► │   n8n   │
│  NutriLens   │ ◄── nutrition result ── │         │
│  port 8000   │                          └─────────┘
└──────────────┘
```

| Komponen | Tugas |
|----------|--------|
| **Browser** | Live camera, kirim frame ke Flask, submit deteksi ke Laravel |
| **Flask (`Yolo/`)** | Inferensi YOLO11 — 21 kelas makanan Indonesia |
| **Laravel (`nutritions-calculator/`)** | Auth, UI, database, orkestrasi webhook |
| **n8n** | Lookup & perhitungan nutrisi (kalori, protein, karbo, dll.) |

---

## Struktur Repository

```text
Project_Candi_JST/
├── nutritions-calculator/   # Laravel 13 — web app & REST API
├── Yolo/                    # Ultralytics YOLO11 + Flask API
│   ├── api/                 # Flask app (detector, routes)
│   ├── train.py             # Training script
│   ├── detect.py            # CLI inference
│   ├── run.py               # Jalankan Flask API
│   └── INTEGRATION.md       # Kontrak API & n8n detail
└── README.md                # File ini
```

---

## Requirements

| Tool | Versi |
|------|--------|
| PHP | 8.3+ |
| Composer | latest |
| Node.js | 18+ |
| npm | latest |
| Python | 3.10+ |
| MySQL | 8+ (atau SQLite untuk dev) |
| n8n | self-hosted / cloud (opsional saat dev) |

**Catatan:** File model (`*.pt`), folder `runs/`, `venv/`, dan `.env` **tidak** di-commit (lihat `.gitignore` masing-masing folder). Setelah clone, train ulang model atau simpan weights secara terpisah.

---

## Quick Start

### 1. Clone repository

```bash
git clone https://github.com/<username>/Project_Candi_JST.git
cd Project_Candi_JST
```

### 2. Flask YOLO API

```bash
cd Yolo
python -m venv venv

# Windows
venv\Scripts\activate

# Linux / macOS
source venv/bin/activate

pip install -r requirements.txt
copy .env.example .env   # Windows
# cp .env.example .env   # Linux / macOS
```

Pastikan weights ada di `runs/detect/indonesia-food-v2/weights/best.pt`. Jika belum:

```bash
python train.py
```

Jalankan API:

```bash
python run.py
# → http://localhost:5000
```

Test health check:

```bash
curl http://localhost:5000/health
```

### 3. Laravel NutriLens

```bash
cd nutritions-calculator
composer install
npm install

copy .env.example .env   # Windows
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
# → http://localhost:8000
```

User demo (dari seeder):

| Field | Value |
|-------|-------|
| Email | `angela@nutrilens.test` |
| Password | `password123` |

### 4. Environment penting

**Laravel** (`nutritions-calculator/.env`):

```env
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=nutritions_calculator
# ... host, user, password

VITE_YOLO_API_URL=http://localhost:5000

N8N_WEBHOOK_URL=https://your-n8n-instance/webhook/nutrition-lookup
N8N_WEBHOOK_SECRET=your_shared_secret
```

**Flask** (`Yolo/.env`):

```env
MODEL_PATH=runs/detect/indonesia-food-v2/weights/best.pt
CONF_THRESHOLD=0.35
FLASK_PORT=5000
FLASK_CORS_ORIGINS=http://localhost:8000
```

---

## Menjalankan (Development)

Buka **2 terminal**:

```bash
# Terminal 1 — YOLO API
cd Yolo && venv\Scripts\activate && python run.py

# Terminal 2 — Laravel
cd nutritions-calculator && php artisan serve
```

Atau di folder Laravel:

```bash
composer dev   # server + queue + vite sekaligus
```

---

## API Reference (Ringkas)

### Flask — `http://localhost:5000`

| Method | Endpoint | Keterangan |
|--------|----------|------------|
| GET | `/health` | Status server & model |
| GET | `/api/classes` | Daftar 21 kelas makanan |
| POST | `/api/detect/frame` | Deteksi dari frame kamera (`multipart`, field `frame`) |

Response deteksi:

```json
{ "food_name": "Rendang", "confidence": 0.87 }
```

### Laravel — `http://localhost:8000/api`

| Method | Endpoint | Auth | Keterangan |
|--------|----------|------|------------|
| POST | `/login` | — | Dapat Bearer token |
| POST | `/meal-logs` | Bearer | Submit deteksi (`food_name`, `confidence`, `meal_type`) |
| GET | `/home` | Bearer | Meal hari ini + ringkasan nutrisi |
| POST | `/webhook/nutrition-result` | Secret header | Callback dari n8n |

Koleksi Postman: [`nutritions-calculator/nutrilens_api_collection.json`](nutritions-calculator/nutrilens_api_collection.json)

Dokumentasi lengkap Laravel: [`nutritions-calculator/README.md`](nutritions-calculator/README.md)

Dokumentasi integrasi Flask & n8n: [`Yolo/INTEGRATION.md`](Yolo/INTEGRATION.md)

---

## Kelas Makanan (YOLO — 21 class)

Ayam Bakar, Ayam Goreng, Bakso, Capcay, Donat, Ikan Bakar, Ikan Goreng, Kentang Goreng, Kentang Rebus, Nasi Putih, Puding, Rendang, Roti Tawar, Sate, Sayur Sop, Tahu Goreng, Telur Ceplok, Telur Dadar, Telur Rebus, Tempe Goreng, Tumis kangkung

Dataset: [Indonesia Food — Roboflow](https://universe.roboflow.com/yolo-obopn/indonesia-food-tjvly)

---

## Testing Flask API

```powershell
# Health
Invoke-RestMethod http://localhost:5000/health

# Deteksi gambar
curl.exe -X POST http://localhost:5000/api/detect/frame `
  -F "frame=@Yolo/Indonesia-Food-1/test/images/<nama-file>.jpg"
```

Atau lewat UI: login NutriLens → klik ikon kamera di slot makanan → arahkan ke makanan.

---

## n8n Workflow (Overview)

1. **Webhook trigger** — terima dari Laravel: `meal_log_id`, `food_name`, `confidence`, `meal_type`, `date`, `user_id`
2. **Lookup nutrisi** — tabel statis / API / AI berdasarkan `food_name`
3. **HTTP Request** — POST ke Laravel:

```http
POST http://localhost:8000/api/webhook/nutrition-result
X-Webhook-Secret: your_shared_secret
Content-Type: application/json

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

Tanpa n8n, meal log tetap `pending`. Untuk uji manual, kirim request di atas via Postman.

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `model_loaded: false` | Train model: `cd Yolo && python train.py` |
| CORS error di browser | Sesuaikan `FLASK_CORS_ORIGINS` dengan `APP_URL` Laravel |
| Flask tidak terjangkau dari UI | Pastikan `VITE_YOLO_API_URL` benar & `python run.py` aktif |
| Deteksi selalu `null` | Turunkan `CONF_THRESHOLD` di `Yolo/.env`, restart Flask |
| Meal stuck `pending` | Setup n8n atau test webhook manual ke Laravel |

---

## Lisensi & Dataset

- Dataset makanan Indonesia: [CC BY 4.0](https://creativecommons.org/licenses/by/4.0/) via Roboflow
- Ultralytics YOLO: [AGPL-3.0](https://github.com/ultralytics/ultralytics/blob/main/LICENSE)

---

## Kontribusi

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/nama-fitur`)
3. Commit perubahan (`git commit -m 'Add: deskripsi singkat'`)
4. Push ke branch (`git push origin feature/nama-fitur`)
5. Buka Pull Request
