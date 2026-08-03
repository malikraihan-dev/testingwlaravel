# Panduan Pasang Kit Attendance App ke Project Laravel `testing31`

## 1. Copy semua file/folder ke project Laravel kamu

Salin isi folder ini ke folder project `testing31`, **timpa/gabung** sesuai struktur folder yang sama:

```
database/migrations/2025_01_01_000001_add_role_to_users_table.php  -> database/migrations/
database/migrations/2025_01_01_000002_create_attendances_table.php -> database/migrations/
database/seeders/AdminSeeder.php                                    -> database/seeders/
app/Models/User.php               -> app/Models/ (TIMPA file lama)
app/Models/Attendance.php         -> app/Models/
app/Http/Middleware/AdminMiddleware.php -> app/Http/Middleware/
app/Http/Controllers/Auth/LoginController.php -> app/Http/Controllers/Auth/
app/Http/Controllers/AttendanceController.php -> app/Http/Controllers/
app/Http/Controllers/DashboardController.php  -> app/Http/Controllers/
app/Http/Controllers/Admin/UserController.php -> app/Http/Controllers/Admin/
app/Http/Controllers/Admin/AttendanceController.php -> app/Http/Controllers/Admin/
routes/web.php                    -> routes/ (TIMPA file lama)
resources/views/...               -> resources/views/ (semua folder & file)
```

## 2. Daftarkan middleware `admin`

Buka file **`bootstrap/app.php`** di root project, cari bagian `->withMiddleware(...)`, dan tambahkan alias seperti ini:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

Kalau file `bootstrap/app.php` belum punya blok `->withMiddleware(...)` sama sekali, tambahkan sebelum `->create()`, contoh lengkap:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

## 3. Jalankan migration & seeder

Buka terminal di folder project, jalankan:

```bash
php artisan migrate
php artisan db:seed --class=AdminSeeder
```

Ini akan membuat kolom `role` di tabel users, tabel `attendances`, dan 1 akun admin default:

```
Email:    admin@example.com
Password: password123
```

## 4. Jalankan server

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000` — otomatis diarahkan ke halaman login.

## 5. Coba alurnya

1. Login pakai akun admin default di atas
2. Masuk ke **Kelola User** → tambah beberapa akun dengan role `user`
3. Logout, login pakai akun `user` yang baru dibuat → coba **Check-in / Check-out** di dashboard
4. Login lagi sebagai admin → buka **Kelola Absensi** untuk lihat/ubah status semua user, dan lihat **grafik kehadiran** di Dashboard Admin

## Catatan

- Password minimal 6 karakter saat membuat/edit user.
- Satu user hanya bisa 1 data absensi per hari (check-in sekali, check-out sekali).
- Status absensi: `hadir`, `izin`, `sakit`, `alpa` — default otomatis `hadir` saat check-in, admin bisa ubah manual di halaman Kelola Absensi (misal untuk tandai izin/sakit/alpa walau user tidak check-in).
