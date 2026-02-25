<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Services;

use DanDoeTech\OpenApiGenerator\Contracts\ModelMetaProviderInterface;
use DanDoeTech\OpenApiGenerator\Support\ResourceResolver;
use DanDoeTech\ResourceRegistry\Registry\Registry;

final readonly class OpenApiGenerator
{
    public function __construct(
        private Registry $registry,
        private ?ModelMetaProviderInterface $modelMetaProvider,
    ) {
    }

    public function create(): array
    {
        $cfg = (array) config('openapi');

        $title   = (string) ($cfg['title'] ?? 'API');
        $version = (string) ($cfg['version'] ?? '1.0.0');
        $prefix  = (string) ($cfg['route_prefix'] ?? config('generic_api.prefix', 'api'));
        $base    = (string) ($cfg['server_base'] ?? config('app.url', ''));

        // Build server URL: APP_URL + /prefix  (handles missing slash)
        $serverUrl = rtrim($base, '/') . '/' . trim($prefix, '/');

        $resolver  = new ResourceResolver($this->modelMetaProvider);
        $generator = new \DanDoeTech\OpenApiGenerator\OpenApi\OpenApiGenerator(
            $resolver,
            title: $title,
            version: $version,
            servers: [['url' => $serverUrl, 'description' => 'Primary API']],
        );

        $doc = $generator->generate($this->registry);

        return $doc->toArray();
    }
}
