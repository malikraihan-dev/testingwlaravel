<?php

use App\Http\Controllers\Admin\AiController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaceEnrollController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Attendance milik user sendiri
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkin');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkout');

    // Verifikasi wajah
    Route::get('/attendance/face-enroll', [FaceEnrollController::class, 'showEnrollForm'])->name('attendance.face-enroll');
    Route::post('/attendance/face-enroll', [FaceEnrollController::class, 'store'])->name('attendance.face-enroll.store');
    Route::delete('/attendance/face-enroll', [FaceEnrollController::class, 'destroy'])->name('attendance.face-enroll.destroy');

    // Khusus admin
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);

        Route::get('attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
        Route::put('attendances/{attendance}', [AdminAttendanceController::class, 'update'])->name('attendances.update');
        Route::delete('attendances/{attendance}', [AdminAttendanceController::class, 'destroy'])->name('attendances.destroy');
        Route::get('attendances-chart-data', [AdminAttendanceController::class, 'chartData'])->name('attendances.chart-data');

        Route::post('ai/chat', [AiController::class, 'chat'])->name('ai.chat');
    });
});
