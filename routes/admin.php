<?php

use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PdfExportController;
use App\Http\Controllers\PerformanceReportController;
use App\Http\Controllers\ProsecutorWeeklyReportController;
use App\Support\RegisterCatalog;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    foreach (RegisterCatalog::sections() as $section) {
        Route::middleware('role:'.$section['group'])->group(function () use ($section) {
            foreach ($section['keys'] as $key) {
                $definition = RegisterCatalog::get($key);
                $controller = $definition['controller'];

                Route::resource($definition['uri'], $controller)
                    ->parameters([$definition['uri'] => 'record'])
                    ->except(['show']);

                if ($definition['review']) {
                    Route::post($definition['uri'].'/{record}/approve', [$controller, 'approve'])->name($definition['uri'].'.approve');
                    Route::post($definition['uri'].'/{record}/reject', [$controller, 'reject'])->name($definition['uri'].'.reject');
                }

                if ($definition['publish']) {
                    Route::post($definition['uri'].'/{record}/publish', [$controller, 'publish'])->name($definition['uri'].'.publish');
                }
            }
        });
    }

    Route::middleware('role:reports')->group(function () {
        Route::get('prosecutor-weekly/export/pdf', [PdfExportController::class, 'prosecutorWeeklyCollection'])->name('prosecutor-weekly.bundle.pdf');
        Route::get('prosecutor-weekly/export/excel', [ExcelExportController::class, 'prosecutorWeeklyCollection'])->name('prosecutor-weekly.bundle.excel');

        Route::resource('prosecutor-weekly', ProsecutorWeeklyReportController::class)
            ->parameters(['prosecutor-weekly' => 'report'])
            ->except(['show']);

        Route::post('prosecutor-weekly/{report}/approve', [ProsecutorWeeklyReportController::class, 'approve'])
            ->middleware('role:reviewers')
            ->name('prosecutor-weekly.approve');
        Route::post('prosecutor-weekly/{report}/reject', [ProsecutorWeeklyReportController::class, 'reject'])
            ->middleware('role:reviewers')
            ->name('prosecutor-weekly.reject');
        Route::get('prosecutor-weekly/{report}/pdf', [PdfExportController::class, 'prosecutorWeekly'])->name('prosecutor-weekly.pdf');
        Route::get('prosecutor-weekly/{report}/excel', [ExcelExportController::class, 'prosecutorWeekly'])->name('prosecutor-weekly.excel');
    });

    Route::middleware('role:structure')->group(function () {
        Route::get('offices', [OfficeController::class, 'index'])->name('offices.index');
        Route::get('offices/create', [OfficeController::class, 'create'])->name('offices.create');
        Route::post('offices', [OfficeController::class, 'store'])->name('offices.store');
        Route::get('offices/{organization}', [OfficeController::class, 'show'])->name('offices.show');
        Route::get('offices/{organization}/edit', [OfficeController::class, 'edit'])->name('offices.edit');
        Route::put('offices/{organization}', [OfficeController::class, 'update'])->name('offices.update');
        Route::post('offices/{organization}/deactivate', [OfficeController::class, 'deactivate'])->name('offices.deactivate');
    });

    Route::middleware('role:experts')->group(function () {
        Route::get('experts', [ExpertController::class, 'index'])->name('experts.index');
        Route::get('experts/create', [ExpertController::class, 'create'])->name('experts.create');
        Route::post('experts', [ExpertController::class, 'store'])->name('experts.store');
        Route::get('experts/{user}', [ExpertController::class, 'show'])->name('experts.show');
        Route::get('experts/{user}/edit', [ExpertController::class, 'edit'])->name('experts.edit');
        Route::put('experts/{user}', [ExpertController::class, 'update'])->name('experts.update');
        Route::post('experts/{user}/deactivate', [ExpertController::class, 'deactivate'])->name('experts.deactivate');
    });

    Route::middleware('role:performance')->group(function () {
        Route::get('performance', [PerformanceReportController::class, 'index'])->name('performance.index');
        Route::get('performance/create', [PerformanceReportController::class, 'create'])->name('performance.create');
        Route::post('performance', [PerformanceReportController::class, 'store'])->name('performance.store');
        Route::get('performance/{report}', [PerformanceReportController::class, 'show'])->name('performance.show');
        Route::get('performance/{report}/edit', [PerformanceReportController::class, 'edit'])->name('performance.edit');
        Route::put('performance/{report}', [PerformanceReportController::class, 'update'])->name('performance.update');
        Route::delete('performance/{report}', [PerformanceReportController::class, 'destroy'])->name('performance.destroy');
        Route::post('performance/{report}/approve', [PerformanceReportController::class, 'approve'])->name('performance.approve');
        Route::post('performance/{report}/reject', [PerformanceReportController::class, 'reject'])->name('performance.reject');
    });

    Route::get('exports/{key}/pdf', [PdfExportController::class, 'download'])->name('exports.pdf');
    Route::get('exports/{key}/excel', [ExcelExportController::class, 'download'])->name('exports.excel');
});
