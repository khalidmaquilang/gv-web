<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            $featuresPath = app_path('Features');
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($featuresPath));

            foreach ($iterator as $file) {
                if (
                    $file->isFile()
                    && $file->getFilename() === 'api.php'
                ) {
                    $api_routes = $file->getPathname();

                    Route::prefix('api/v1')
                        ->name('api.')
                        ->group($api_routes);
                }
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
