# NutriLens

Aplikasi kalkulator nutrisi berbasis foto makanan. Upload foto → AI analisis kandungan gizi → tampil ringkasan kalori, protein, karbo, lemak, serat harian.

**Stack:** Laravel 13 · MySQL · Blade · Tailwind CSS v4 · Laravel Sanctum · n8n webhook

---

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ & npm
- MySQL 8+

---

## Setup

### 1. Clone & install dependencies

```bash
git clone https://github.com/AndhikaBPN/nutritions-calculator.git
cd nutritions-calculator

composer install
npm install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — sesuaikan koneksi MySQL dan n8n:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nutritions_calculator
DB_USERNAME=root
DB_PASSWORD=

# n8n webhook — Laravel sends food_name only (no image)
N8N_WEBHOOK_URL=https://your-n8n-instance/webhook/nutrition-lookup
N8N_WEBHOOK_SECRET=your_secret

# Flask YOLO API URL for browser camera UI
VITE_YOLO_API_URL=http://localhost:5000
```

### 3. Database

Buat database terlebih dahulu:

```sql
CREATE DATABASE nutritions_calculator;
```

Kemudian jalankan migration dan seeder:

```bash
php artisan migrate --seed
```

Seeder akan membuat user test:

| Field    | Value                    |
|----------|--------------------------|
| Email    | `angela@nutrilens.test`  |
| Password | password123              |

### 4. Storage link

```bash
php artisan storage:link
```

### 5. Build assets

```bash
npm run build
```

---

## Menjalankan aplikasi

```bash
composer dev
```

Perintah ini menjalankan server, queue worker, log viewer, dan Vite secara bersamaan.

Atau jalankan terpisah:

```bash
php artisan serve    # http://localhost:8000
npm run dev          # Vite HMR
```

---

## Testing API (Postman)

Import file `nutrilens_api_collection.json` ke Postman.

Collection variable sudah diset ke `http://localhost:8000/api`. Setelah import:

1. Jalankan request **Login** → token otomatis tersimpan ke variable `{{token}}`
2. Semua request lain langsung terautentikasi via Bearer token

**Endpoint tersedia:**

| Method | Endpoint | Auth | Keterangan |
| ------ | -------- | ---- | ---------- |
| POST | `/api/login` | - | Login, dapat Bearer token |
| POST | `/api/register` | - | Registrasi user baru |
| POST | `/api/forgot-password` | - | Kirim link reset password |
| GET | `/api/me` | Bearer | Data user yang login |
| POST | `/api/logout` | Bearer | Hapus token aktif |
| GET | `/api/home` | Bearer | Meal hari ini + daily summary |
| POST | `/api/meal-logs` | Bearer | Submit deteksi makanan (JSON: food_name, confidence) |
| GET | `/api/meal-logs/today` | Bearer | Daftar meal hari ini |
| DELETE | `/api/meal-logs/{id}` | Bearer | Hapus meal log |
| GET | `/api/history` | Bearer | Riwayat meal (filter date) |
| GET | `/api/profile` | Bearer | Data profil user |
| PUT | `/api/profile` | Bearer | Update profil + foto |
| GET | `/api/chart-data` | Bearer | Data chart nutrisi bulanan |
| POST | `/api/webhook/nutrition-result` | Secret header | Callback dari n8n |

**Query parameters pagination** (endpoint list):

```http
?page=1&limit=10
```

**Response format standar:**

```json
{
    "code": 200,
    "message": "Ok",
    "data": { ... },
    "meta": {
        "page": 1,
        "limit": 10,
        "total": 42,
        "last_page": 5
    }
}
```

`meta` hanya muncul jika pakai `?limit=`.

---

## Arsitektur

```text
app/
├── Enums/              # MealType, CalorieStatus, Gender, MealLogStatus
├── Http/
│   ├── Controllers/
│   │   └── Api/        # API controllers (return JSON)
│   └── Requests/       # StoreMealLogRequest, UpdateProfileRequest
├── Models/             # User, MealLog, MealAiResult, DailySummary, YoloLabel
├── Repositories/       # MealLogRepository — semua DB query
├── Services/           # AuthService, MealLogService, NutritionService, ProfileService, WebhookService
└── Traits/
    └── ApiResponsable.php  # Standar response helper
```

**Data flow:**

1. Browser capture frame kamera → POST ke Flask `/api/detect/frame` → dapat `food_name` + `confidence`
2. Browser submit deteksi ke Laravel `POST /api/meal-logs` → buat `MealLog` (status: pending)
3. Laravel kirim `food_name` ke n8n (tanpa gambar) via `WebhookService`
4. n8n lookup nutrisi → POST hasil ke `/api/webhook/nutrition-result`
5. `WebhookController` buat `MealAiResult` + update status → `NutritionService` recalculate `DailySummary`

**Integrasi kamera (browser):**

```javascript
const YOLO_API = import.meta.env.VITE_YOLO_API_URL; // http://localhost:5000

// 1. getUserMedia() → video stream
// 2. Setiap 300–500ms: canvas → blob → POST `${YOLO_API}/api/detect/frame`
// 3. Jika food_name stabil (confidence >= 0.7, 3x berturut) → POST /api/meal-logs
// 4. Poll GET /api/home sampai status === "done"
```

**Kontrak n8n inbound (dari Laravel):**

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

---

## Commands

```bash
composer dev          # Jalankan semua (server + queue + log + vite)
php artisan test      # Jalankan test suite
./vendor/bin/pint     # Lint (Laravel Pint)
php artisan migrate:fresh --seed   # Reset database + seed ulang
```
