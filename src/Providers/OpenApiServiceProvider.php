<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Providers;

use DanDoeTech\LaravelOpenApiGenerator\Console\OpenApiExportCommand;
use Illuminate\Support\ServiceProvider;

final class OpenApiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/ddt_openapi.php', 'ddt_openapi');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/ddt_openapi.php' => $this->app->configPath('ddt_openapi.php'),
        ], 'ddt-openapi-config');

        if ($this->app->runningInConsole()) {
            $this->commands([OpenApiExportCommand::class]);
        }
    }
}
