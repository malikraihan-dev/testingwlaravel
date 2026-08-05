# Update: Kelola Wajah (Admin) + Kelola Dokumen Teamwork (v6)

Dua fitur baru:

**A. Kelola Wajah (Admin)** — admin bisa lihat semua user & status verifikasi wajahnya, upload foto untuk daftarkan/ganti wajah user (AI ekstrak fitur wajah dari foto yang diupload), dan hapus data wajah user.

**B. Kelola Dokumen (Teamwork)** — semua user bisa upload dokumen kerja (Word/PDF/Excel), undang user lain jadi kolaborator, kolaborator bisa upload versi baru & lihat riwayat versi. Admin punya halaman terpisah untuk memantau/menghapus semua dokumen.

Kit ini menambah di atas kit-kit sebelumnya (v1-v5). Pasang itu dulu kalau belum.

## 1. Copy file-file berikut ke project

```
database/migrations/2025_01_05_000001_add_face_photo_path_to_users_table.php   -> database/migrations/
database/migrations/2025_01_05_000002_create_documents_table.php                -> database/migrations/
database/migrations/2025_01_05_000003_create_document_versions_table.php        -> database/migrations/
database/migrations/2025_01_05_000004_create_document_collaborators_table.php   -> database/migrations/

app/Models/User.php                          -> app/Models/ (TIMPA)
app/Models/Document.php                      -> app/Models/ (BARU)
app/Models/DocumentVersion.php               -> app/Models/ (BARU)

app/Http/Controllers/FaceEnrollController.php          -> app/Http/Controllers/ (TIMPA)
app/Http/Controllers/DocumentController.php             -> app/Http/Controllers/ (BARU)
app/Http/Controllers/Admin/FaceController.php             -> app/Http/Controllers/Admin/ (BARU)
app/Http/Controllers/Admin/DocumentController.php          -> app/Http/Controllers/Admin/ (BARU)

resources/views/attendance/face-enroll.blade.php        -> resources/views/attendance/ (TIMPA)
resources/views/admin/face/index.blade.php                -> resources/views/admin/face/ (folder baru)
resources/views/admin/face/edit.blade.php                 -> resources/views/admin/face/
resources/views/documents/index.blade.php                  -> resources/views/documents/ (folder baru)
resources/views/documents/create.blade.php                 -> resources/views/documents/
resources/views/documents/show.blade.php                    -> resources/views/documents/
resources/views/admin/documents/index.blade.php              -> resources/views/admin/documents/ (folder baru)
resources/views/layouts/app.blade.php                          -> resources/views/layouts/ (TIMPA)

routes/web.php                                                    -> routes/ (TIMPA)
```

## 2. Jalankan migration

```bash
php artisan migrate
```

## 3. Pastikan storage link sudah ada

```bash
php artisan storage:link
```

(Kalau muncul "symlink already exists", aman — lanjut saja.)

## 4. Jalankan & coba alurnya

```bash
php artisan serve
```

### Kelola Wajah (login sebagai admin)

1. Buka menu **Kelola Wajah**
2. Klik **Daftarkan** / **Ganti Foto** pada salah satu user
3. Upload foto wajah yang jelas — AI otomatis mendeteksi wajah dari foto tersebut (tunggu status "Wajah terdeteksi")
4. Klik **Simpan**
5. Untuk menghapus, klik **Hapus** pada baris user yang sudah aktif verifikasinya

### Kelola Dokumen (login sebagai user manapun)

1. Buka menu **Dokumen** → **Upload Dokumen**
2. Isi judul, deskripsi (opsional), pilih file Word/PDF/Excel → Upload
3. Di halaman detail dokumen:
   - **Upload Versi Baru** — bisa dilakukan pemilik maupun kolaborator
   - **Kolaborator** — hanya pemilik yang bisa menambah/mengeluarkan kolaborator (pilih dari dropdown)
   - **Riwayat Versi** — semua versi tersimpan dan bisa diunduh kapan saja
4. Login sebagai user lain yang sudah diundang jadi kolaborator → dokumen itu otomatis muncul di menu **Dokumen** miliknya juga

### Kelola Dokumen (admin)

Menu **Kelola Dokumen** di sidebar admin menampilkan semua dokumen dari semua user, dengan opsi hapus untuk moderasi.

## Catatan penting

- Foto & file dokumen disimpan di `storage/app/public/face-photos` dan `storage/app/public/documents` — pastikan `storage:link` sudah dijalankan supaya bisa diakses lewat browser.
- Format dokumen yang diterima: `.doc`, `.docx`, `.pdf`, `.xls`, `.xlsx`, maksimal 10MB per file.
- Kolaborator **tidak bisa** menghapus dokumen atau mengelola kolaborator lain — itu hak khusus pemilik. Kolaborator hanya bisa lihat & upload versi baru (sesuai yang diminta).
- Model AI wajah (`public/models`) yang sudah kamu download sebelumnya dipakai ulang di sini — tidak perlu download ulang.
