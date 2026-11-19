<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicPortalController;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPortalController::class, 'home'])->name('home');
Route::get('/cases-tracking', [PublicPortalController::class, 'cases'])->name('cases.tracking');
Route::get('/laws', [PublicPortalController::class, 'laws'])->name('laws.index');
Route::get('/laws/{law}/download', [PublicPortalController::class, 'downloadLaw'])->name('laws.download');
Route::get('/community', [PublicPortalController::class, 'community'])->name('community.index');
Route::post('/community/feedback', [PublicPortalController::class, 'storeFeedback'])->name('community.feedback');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:'.Roles::ALL)
        ->name('dashboard');
    Route::get('/zone-center', [DashboardController::class, 'zone'])
        ->middleware('role:'.Roles::ZONE)
        ->name('zone-center');
    Route::get('/woredas', [DashboardController::class, 'woredas'])
        ->middleware('role:'.Roles::ALL)
        ->name('woredas');

    require __DIR__.'/admin.php';
});
