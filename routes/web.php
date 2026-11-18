<?php

use App\Http\Controllers\Admin\AnnouncementController as AdminAnnouncementController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComplaintController as AdminComplaintController;
use App\Http\Controllers\Admin\ComplaintDispositionController;
use App\Http\Controllers\Admin\ComplaintStatusController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EscalationController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\OfficerController;
use App\Http\Controllers\Admin\QrLocationController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ComplaintCancellationController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ComplaintRatingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PublicMapController;
use App\Http\Controllers\QrScanController;
use App\Http\Controllers\ReadNotificationsController;
use App\Http\Controllers\TrackingController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('beranda');

Route::get('/lacak', TrackingController::class)->name('lacak')->middleware('throttle:10,1');
Route::get('/peta', PublicMapController::class)->name('peta');
Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('pengumuman.index');
Route::get('/pengumuman/{announcement}', [AnnouncementController::class, 'show'])->name('pengumuman.show');
Route::get('/faq', FaqController::class)->name('faq');
Route::get('/qr/{qrLocation:code}', QrScanController::class)->name('qr.scan');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store')->middleware('throttle:5,1');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store')->middleware('throttle:5,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Aplikasi Masyarakat
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:masyarakat'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/pengaduan', [ComplaintController::class, 'index'])->name('pengaduan.index');
    Route::get('/pengaduan/buat', [ComplaintController::class, 'create'])->name('pengaduan.create');
    Route::post('/pengaduan', [ComplaintController::class, 'store'])->name('pengaduan.store')->middleware('throttle:5,60');
    Route::get('/pengaduan/{complaint}', [ComplaintController::class, 'show'])->name('pengaduan.show');
    Route::patch('/pengaduan/{complaint}/batal', ComplaintCancellationController::class)->name('pengaduan.cancel');
    Route::post('/pengaduan/{complaint}/penilaian', ComplaintRatingController::class)->name('pengaduan.rating');
    Route::post('/notifikasi/baca', ReadNotificationsController::class)->name('notifikasi.read');
});

/*
|--------------------------------------------------------------------------
| Panel Admin (Kecamatan & Kabupaten)
|--------------------------------------------------------------------------
*/
$adminComplaintRoutes = function (): void {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::get('/pengaduan', [AdminComplaintController::class, 'index'])->name('pengaduan.index');
    Route::get('/pengaduan/{complaint}', [AdminComplaintController::class, 'show'])->name('pengaduan.show');
    Route::patch('/pengaduan/{complaint}/status', ComplaintStatusController::class)->name('pengaduan.status');
    Route::post('/pengaduan/{complaint}/disposisi', ComplaintDispositionController::class)->name('pengaduan.disposisi');
    Route::resource('qr', QrLocationController::class)
        ->only(['index', 'store', 'update'])
        ->parameters(['qr' => 'qrLocation']);
};

Route::middleware(['auth', 'role:admin_kecamatan'])
    ->prefix('kecamatan')
    ->name('kecamatan.')
    ->group($adminComplaintRoutes);

Route::middleware(['auth', 'role:admin_kabupaten'])
    ->prefix('kabupaten')
    ->name('kabupaten.')
    ->group(function () use ($adminComplaintRoutes): void {
        $adminComplaintRoutes();

        Route::get('/eskalasi', EscalationController::class)->name('eskalasi');
        Route::resource('kategori', CategoryController::class)
            ->only(['index', 'store', 'update'])
            ->parameters(['kategori' => 'category']);
        Route::resource('petugas', OfficerController::class)
            ->only(['index', 'store', 'update'])
            ->parameters(['petugas' => 'officer']);
        Route::resource('pengumuman', AdminAnnouncementController::class)
            ->only(['index', 'store', 'update'])
            ->parameters(['pengumuman' => 'announcement']);
        Route::resource('faq', AdminFaqController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
