<?php

use App\Support\RegisterCatalog;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    foreach (RegisterCatalog::sections() as $section) {
        Route::middleware('role:'.$section['roles'])->group(function () use ($section) {
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
});
