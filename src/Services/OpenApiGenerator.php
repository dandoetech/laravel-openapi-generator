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

    /** @return array<string, mixed> */
    public function create(): array
    {
        $cfg = (array) config('ddt_openapi');

        $title = \is_string($cfg['title'] ?? null) ? $cfg['title'] : 'API';
        $version = \is_string($cfg['version'] ?? null) ? $cfg['version'] : '1.0.0';

        $cfgPrefix = $cfg['route_prefix'] ?? config('ddt_api.prefix', 'api');
        $prefix = \is_string($cfgPrefix) ? $cfgPrefix : 'api';

        $cfgBase = $cfg['server_base'] ?? config('app.url', '');
        $base = \is_string($cfgBase) ? $cfgBase : '';

        // Build server URL: APP_URL + /prefix  (handles missing slash)
        $serverUrl = \rtrim($base, '/') . '/' . \trim($prefix, '/');

        $resolver = new ResourceResolver($this->modelMetaProvider);
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
