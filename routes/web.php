<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'home'])->name('home');
Route::get('/cases-tracking', [PublicPortalController::class, 'cases'])->name('cases.tracking');
Route::get('/laws', [PublicPortalController::class, 'laws'])->name('laws.index');
Route::get('/laws/{law}/download', [PublicPortalController::class, 'downloadLaw'])->name('laws.download');
Route::get('/community', [PublicPortalController::class, 'community'])->name('community.index');
Route::post('/community/feedback', [PublicPortalController::class, 'storeFeedback'])
    ->middleware('throttle:5,1')
    ->name('community.feedback');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:all')
        ->name('dashboard');
    Route::get('/zone-center', [DashboardController::class, 'zone'])
        ->middleware('role:zone')
        ->name('zone-center');
    Route::get('/woredas', [DashboardController::class, 'woredas'])
        ->middleware('role:all')
        ->name('woredas');
    Route::get('/settings', [SettingController::class, 'edit'])
        ->middleware('role:all')
        ->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])
        ->middleware('role:settings')
        ->name('settings.update');
    Route::get('/permissions', [PermissionController::class, 'edit'])
        ->middleware('role:permissions')
        ->name('permissions.edit');
    Route::put('/permissions', [PermissionController::class, 'update'])
        ->middleware('role:permissions')
        ->name('permissions.update');

    require __DIR__.'/admin.php';
});
