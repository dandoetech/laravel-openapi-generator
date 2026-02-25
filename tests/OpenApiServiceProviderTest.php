<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Tests;

use DanDoeTech\LaravelOpenApiGenerator\Providers\OpenApiServiceProvider;
use Orchestra\Testbench\TestCase;

final class OpenApiServiceProviderTest extends TestCase
{
    /**
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [OpenApiServiceProvider::class];
    }

    public function testServiceProviderBoots(): void
    {
        \assert($this->app !== null);
        $this->assertTrue($this->app->providerIsLoaded(OpenApiServiceProvider::class));
    }

    public function testConfigIsMerged(): void
    {
        $this->assertNotNull(config('openapi'));
    }
}
