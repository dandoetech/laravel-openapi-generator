<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Console;

use DanDoeTech\OpenApiGenerator\Contracts\ModelMetaProviderInterface;
use DanDoeTech\OpenApiGenerator\OpenApi\OpenApiGenerator;
use DanDoeTech\OpenApiGenerator\Support\ResourceResolver;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;

final class OpenApiExportCommand extends Command
{
    protected $signature = 'openapi:export
        {--output= : Relative to storage_path(); defaults to config(ddt_openapi.output)}
        {--title= : Overrides config(ddt_openapi.title)}
        {--oaversion= : Overrides config(ddt_openapi.version)}';

    protected $description = 'Export OpenAPI 3.1 document generated from Resource Registry (with model meta fallback).';

    public function handle(Registry $registry, FilesystemFactory $storage): int
    {
        $cfg = (array) config('ddt_openapi');

        $optTitle = $this->option('title');
        $title = \is_string($optTitle) && $optTitle !== '' ? $optTitle : (\is_string($cfg['title'] ?? null) ? $cfg['title'] : 'API');

        $optVersion = $this->option('oaversion');
        $version = \is_string($optVersion) && $optVersion !== '' ? $optVersion : (\is_string($cfg['version'] ?? null) ? $cfg['version'] : '1.0.0');

        $optOutput = $this->option('output');
        $outRel = \is_string($optOutput) && $optOutput !== '' ? $optOutput : (\is_string($cfg['output'] ?? null) ? $cfg['output'] : 'app/openapi.json');
        $outPath = storage_path($outRel);

        /** @var ModelMetaProviderInterface|null $meta */
        $meta = $this->laravel->bound(ModelMetaProviderInterface::class)
            ? $this->laravel->make(ModelMetaProviderInterface::class)
            : null;

        $resolver = new ResourceResolver($meta);
        $generator = new OpenApiGenerator($resolver, title: $title, version: $version);

        $doc = $generator->generate($registry);
        $json = $doc->toJson();

        $disk = $storage->disk('local');
        $disk->put(\ltrim($outRel, '/'), $json);

        $this->info("OpenAPI exported to: {$outPath}");

        return self::SUCCESS;
    }
}
