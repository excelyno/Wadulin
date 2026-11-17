<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengaduanController;
use App\Models\Pengaduan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('beranda', [
        'totalPengaduan' => Pengaduan::count(),
    ]);
})->name('beranda');

Route::get('/pengaduan/create', [PengaduanController::class, 'create'])->name('pengaduan.create');
Route::post('/pengaduan', [PengaduanController::class, 'store'])->name('pengaduan.store');
Route::get('/pengaduan/{pengaduan}', [PengaduanController::class, 'show'])->name('pengaduan.show');
Route::get('/lacak', [PengaduanController::class, 'track'])->name('pengaduan.track');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/pengaduan', [DashboardController::class, 'geojson'])->name('pengaduan.geojson');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store')->middleware('throttle:5,1');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function (): void {
    Route::patch('/dashboard/pengaduan/{pengaduan}/status', [DashboardController::class, 'updateStatus'])->name('pengaduan.status');
    Route::delete('/dashboard/pengaduan/{pengaduan}', [DashboardController::class, 'destroy'])->name('pengaduan.destroy');
});
