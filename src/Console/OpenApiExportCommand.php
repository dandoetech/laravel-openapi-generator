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
        {--output= : Relative to storage_path(); defaults to config(openapi.output)}
        {--title= : Overrides config(openapi.title)}
        {--oaversion= : Overrides config(openapi.version)}';

    protected $description = 'Export OpenAPI 3.1 document generated from Resource Registry (with model meta fallback).';

    public function handle(Registry $registry, FilesystemFactory $storage): int
    {
        $cfg = (array) config('openapi');

        $title   = (string) ($this->option('title') ?: ($cfg['title'] ?? 'API'));
        $version = (string) ($this->option('oaversion') ?: ($cfg['version'] ?? '1.0.0'));
        $outRel  = (string) ($this->option('output') ?: ($cfg['output'] ?? 'app/openapi.json'));
        $outPath = storage_path($outRel);

        /** @var ModelMetaProviderInterface|null $meta */
        $meta = $this->laravel->bound(ModelMetaProviderInterface::class)
            ? $this->laravel->make(ModelMetaProviderInterface::class)
            : null;

        $resolver  = new ResourceResolver($meta);
        $generator = new OpenApiGenerator($resolver, title: $title, version: $version);

        $doc = $generator->generate($registry);
        $json = $doc->toJson();

        $disk = $storage->disk('local');
        $disk->put(ltrim($outRel, '/'), $json);

        $this->info("OpenAPI exported to: {$outPath}");

        return self::SUCCESS;
    }
}
