<?php

use App\Http\Controllers\ExcelExportController;
use App\Http\Controllers\PdfExportController;
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

    Route::get('exports/{key}/pdf', [PdfExportController::class, 'download'])->name('exports.pdf');
    Route::get('exports/{key}/excel', [ExcelExportController::class, 'download'])->name('exports.excel');
});
