<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Tests\Services;

use DanDoeTech\LaravelOpenApiGenerator\Providers\OpenApiServiceProvider;
use DanDoeTech\LaravelOpenApiGenerator\Services\OpenApiGenerator;
use DanDoeTech\LaravelOpenApiGenerator\Tests\Fixtures\TestCategoryResource;
use DanDoeTech\LaravelOpenApiGenerator\Tests\Fixtures\TestProductResource;
use DanDoeTech\ResourceRegistry\Contracts\RegistryDriverInterface;
use DanDoeTech\ResourceRegistry\Contracts\ResourceDefinitionInterface;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Illuminate\Config\Repository as ConfigRepository;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class OpenApiGeneratorTest extends TestCase
{
    /** @return list<class-string> */
    protected function getPackageProviders($app): array
    {
        return [OpenApiServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        /** @var ConfigRepository $config */
        $config = $app['config'];

        $config->set('ddt_openapi.title', 'Test API');
        $config->set('ddt_openapi.version', '2.0.0');
        $config->set('ddt_api.prefix', 'api/v1');
        $config->set('app.url', 'http://localhost');
    }

    #[Test]
    public function it_generates_valid_openapi_structure(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        $this->assertEquals('3.1.0', $spec['openapi']);
        $this->assertArrayHasKey('info', $spec);
        $this->assertArrayHasKey('paths', $spec);
        $this->assertArrayHasKey('components', $spec);
    }

    #[Test]
    public function it_includes_configured_title_and_version(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array{title: string, version: string} $info */
        $info = $spec['info'];
        $this->assertEquals('Test API', $info['title']);
        $this->assertEquals('2.0.0', $info['version']);
    }

    #[Test]
    public function it_includes_paths_for_each_resource(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array<string, mixed> $paths */
        $paths = $spec['paths'];

        $this->assertArrayHasKey('/product', $paths);
        $this->assertArrayHasKey('/product/{id}', $paths);
        $this->assertArrayHasKey('/category', $paths);
        $this->assertArrayHasKey('/category/{id}', $paths);
    }

    #[Test]
    public function it_includes_correct_http_methods(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array<string, mixed> $paths */
        $paths = $spec['paths'];

        /** @var array<string, mixed> $collection */
        $collection = $paths['/product'];
        $this->assertArrayHasKey('get', $collection);
        $this->assertArrayHasKey('post', $collection);

        /** @var array<string, mixed> $item */
        $item = $paths['/product/{id}'];
        $this->assertArrayHasKey('get', $item);
    }

    #[Test]
    public function it_includes_schema_properties_matching_fields(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array{schemas: array<string, mixed>} $components */
        $components = $spec['components'];
        $schemas = $components['schemas'];

        /** @var array<string, mixed> $productSchema */
        $productSchema = $schemas['Product'];

        $this->assertArrayHasKey('properties', $productSchema);
        /** @var array<string, mixed> $props */
        $props = $productSchema['properties'];

        $this->assertArrayHasKey('name', $props);
        $this->assertArrayHasKey('price', $props);
        $this->assertArrayHasKey('category_id', $props);
    }

    #[Test]
    public function it_includes_computed_fields_in_schema(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array{schemas: array<string, mixed>} $components */
        $components = $spec['components'];
        $schemas = $components['schemas'];

        /** @var array<string, mixed> $productSchema */
        $productSchema = $schemas['Product'];

        /** @var array<string, mixed> $props */
        $props = $productSchema['properties'];

        $this->assertArrayHasKey('category_name', $props);
    }

    #[Test]
    public function it_builds_server_url_from_config(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var list<array{url: string}> $servers */
        $servers = $spec['servers'];

        $this->assertNotEmpty($servers);
        $this->assertStringContainsString('api/v1', $servers[0]['url']);
    }

    #[Test]
    public function it_includes_problem_json_schema(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array{schemas: array<string, mixed>} $components */
        $components = $spec['components'];
        $schemas = $components['schemas'];

        $this->assertArrayHasKey('ProblemJson', $schemas);
    }

    #[Test]
    public function it_includes_both_resources_in_schemas(): void
    {
        $registry = $this->buildRegistry();
        $generator = new OpenApiGenerator($registry, null);

        $spec = $generator->create();

        /** @var array{schemas: array<string, mixed>} $components */
        $components = $spec['components'];
        $schemas = $components['schemas'];

        $this->assertArrayHasKey('Product', $schemas);
        $this->assertArrayHasKey('Category', $schemas);
    }

    private function buildRegistry(): Registry
    {
        $product = new TestProductResource();
        $category = new TestCategoryResource();

        $map = [
            $product->getKey()  => $product,
            $category->getKey() => $category,
        ];

        $driver = new class ($map) implements RegistryDriverInterface {
            /** @param array<string, ResourceDefinitionInterface> $resources */
            public function __construct(private readonly array $resources)
            {
            }

            /** @return list<ResourceDefinitionInterface> */
            public function all(): array
            {
                return \array_values($this->resources);
            }

            public function find(string $key): ?ResourceDefinitionInterface
            {
                return $this->resources[$key] ?? null;
            }
        };

        return new Registry($driver);
    }
}
