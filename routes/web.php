<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PublicPortalController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TwoFactorController;
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
    Route::get('/forgot-password', [PasswordController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordController::class, 'sendLink'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->name('password.update');
    Route::get('/two-factor-challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor-challenge', [TwoFactorController::class, 'confirmChallenge'])->name('two-factor.confirm');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.change');
    Route::get('/two-factor', [TwoFactorController::class, 'edit'])->name('two-factor.edit');
    Route::post('/two-factor', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
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
