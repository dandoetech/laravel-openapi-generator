<?php

namespace DanDoeTech\LaravelOpenApiGenerator\Services;

use DanDoeTech\LaravelOpenApiGenerator\Support\LaravelRegistryFactory;
use DanDoeTech\OpenApiGenerator\Contracts\ModelMetaProviderInterface;
use DanDoeTech\OpenApiGenerator\Support\ResourceResolver;

final readonly class OpenApiGenerator
{
    public function __construct(
        private ?ModelMetaProviderInterface $modelMetaProvider,
    ) {
    }
    public function create(): array
    {
        $cfg = (array) config('openapi');

        $title   = (string) ($cfg['title'] ?? 'API');
        $version = (string) ($cfg['version'] ?? '1.0.0');
        $prefix = (string) ($cfg['route_prefix'] ?? config('generic_api.prefix', 'api'));
        $base   = (string) ($cfg['server_base'] ?? config('app.url', ''));


        // Build server URL: APP_URL + /prefix  (handles missing slash)
        $serverUrl = rtrim($base, '/') . '/' . trim($prefix, '/');

        $resourcesPath = ($cfg['resources_config'] ?? base_path('config/resources.php'));

        // Resolve Registry (bound or from array config)
        $registry = LaravelRegistryFactory::make(app(), is_string($resourcesPath) ? $resourcesPath : null);

        // Resolve model meta provider (composite provided by laravel-model-meta)
        $resolver  = new ResourceResolver($this->modelMetaProvider);
        $generator = new \DanDoeTech\OpenApiGenerator\OpenApi\OpenApiGenerator(
            $resolver,
            title: $title,
            version: $version,
            servers: [['url' => $serverUrl, 'description' => 'Primary API']]
        );

        $doc = $generator->generate($registry);
        return $doc->toArray();
    }
}
