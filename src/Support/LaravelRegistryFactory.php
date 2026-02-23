<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Support;

use DanDoeTech\ResourceRegistry\Registry\ArrayRegistryDriver;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Illuminate\Contracts\Container\Container;

/**
 * Creates or resolves a Registry instance.
 * If an app binding exists (e.g. from a future Laravel bridge), it will be used.
 * Otherwise, it builds from a PHP config file (array driver).
 */
final class LaravelRegistryFactory
{
    public static function make(Container $app, ?string $configPath = null): Registry
    {
        // Prefer an existing binding, if your app already provides one
        if ($app->bound(Registry::class)) {
            return $app->make(Registry::class);
        }

        $config = config('resources') ?? [];

        return new Registry(new ArrayRegistryDriver($config));
        // Later you can swap this with a DB-backed driver, etc.
    }
}
