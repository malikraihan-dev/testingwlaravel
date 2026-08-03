# Panduan Pasang Fitur AI: Verifikasi Wajah saat Check-in

Fitur ini pakai **face-api.js** (model deep learning yang jalan di browser lewat TensorFlow.js).
Tidak perlu server Python atau API berbayar.

## 1. Copy file ke project Laravel `testing31`

```
database/migrations/2025_01_02_000001_add_face_descriptor_to_users_table.php -> database/migrations/
app/Services/FaceMatcher.php                          -> app/Services/
app/Http/Controllers/FaceEnrollController.php          -> app/Http/Controllers/
app/Http/Controllers/AttendanceController.php           -> app/Http/Controllers/ (TIMPA)
resources/views/attendance/face-enroll.blade.php       -> resources/views/attendance/
resources/views/attendance/index.blade.php              -> resources/views/attendance/ (TIMPA)
resources/views/dashboard/user.blade.php                -> resources/views/dashboard/ (TIMPA)
routes/web.php                                           -> routes/ (TIMPA)
```

## 2. Download model AI (wajib, ±6 MB)

Buat folder `public/models` di project Laravel kamu, lalu download 7 file ini dari repo resmi
face-api.js dan taruh semuanya langsung di `public/models` (bukan di subfolder):

https://github.com/justadudewhohacks/face-api.js/tree/master/weights

File yang dibutuhkan:
- `tiny_face_detector_model-weights_manifest.json`
- `tiny_face_detector_model-shard1`
- `face_landmark_68_model-weights_manifest.json`
- `face_landmark_68_model-shard1`
- `face_recognition_model-weights_manifest.json`
- `face_recognition_model-shard1`
- `face_recognition_model-shard2`

Cara cepat lewat terminal (dari root project Laravel):

```bash
mkdir -p public/models
cd public/models
BASE=https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights
curl -O $BASE/tiny_face_detector_model-weights_manifest.json
curl -O $BASE/tiny_face_detector_model-shard1
curl -O $BASE/face_landmark_68_model-weights_manifest.json
curl -O $BASE/face_landmark_68_model-shard1
curl -O $BASE/face_recognition_model-weights_manifest.json
curl -O $BASE/face_recognition_model-shard1
curl -O $BASE/face_recognition_model-shard2
```

## 3. Jalankan migration

```bash
php artisan migrate
```

Ini menambah kolom `face_descriptor` (JSON) di tabel `users` — isinya 128 angka fitur wajah,
**bukan foto**, jadi aman dari sisi privasi data.

## 4. Coba alurnya

1. Login sebagai user biasa (bukan admin).
2. Buka **Absensi Saya** → klik **"Aktifkan Verifikasi Wajah"**.
3. Izinkan akses kamera di browser, tunggu model AI selesai dimuat, posisikan wajah, klik **"Ambil & Simpan Wajah"**.
4. Kembali ke halaman Absensi Saya, sekarang tombolnya jadi **"Check-in (Verifikasi Wajah)"**.
5. Klik, kamera nyala lagi, klik **"Verifikasi & Check-in"** — sistem akan mencocokkan wajah
   dengan data terdaftar sebelum mencatat kehadiran.
6. Coba juga dengan wajah orang lain / foto di layar HP untuk lihat sistem menolak check-in
   kalau wajah tidak cocok (pesan error muncul).

## Catatan penting

- **Wajib HTTPS di production** (atau `localhost` saat development) — browser modern hanya
  izinkan akses kamera (`getUserMedia`) di konteks aman.
- Fitur ini **opsional per user** — kalau user belum mendaftarkan wajah, check-in tetap
  berjalan seperti biasa (tanpa verifikasi), jadi tidak mengganggu user lama.
- Threshold kecocokan wajah ada di `app/Services/FaceMatcher.php` (default `0.5`). Kalau
  terlalu sering gagal cocok padahal orangnya sama, naikkan sedikit ke `0.55`.
- Model face-api.js ini deteksi & bandingkan wajah secara umum — bukan *liveness detection*,
  jadi teorinya masih bisa ditipu pakai foto resolusi tinggi. Untuk versi produksi yang lebih
  aman, bisa ditambah pengecekan kedipan mata atau geofencing sebagai lapisan tambahan.
