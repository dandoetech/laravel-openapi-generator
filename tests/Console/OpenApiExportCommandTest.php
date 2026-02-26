<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Tests\Console;

use DanDoeTech\LaravelOpenApiGenerator\Providers\OpenApiServiceProvider;
use DanDoeTech\LaravelOpenApiGenerator\Tests\Fixtures\TestProductResource;
use DanDoeTech\ResourceRegistry\Contracts\RegistryDriverInterface;
use DanDoeTech\ResourceRegistry\Contracts\ResourceDefinitionInterface;
use DanDoeTech\ResourceRegistry\Registry\Registry;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Testing\PendingCommand;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class OpenApiExportCommandTest extends TestCase
{
    private string $outputRelPath = 'openapi-test.json';

    /** @return list<class-string> */
    protected function getPackageProviders($app): array
    {
        return [OpenApiServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        /** @var ConfigRepository $config */
        $config = $app['config'];

        $config->set('ddt_openapi.title', 'Export Test API');
        $config->set('ddt_openapi.version', '1.0.0');
        $config->set('ddt_openapi.output', $this->outputRelPath);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $product = new TestProductResource();
        $map = [$product->getKey() => $product];

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

        \assert($this->app !== null);
        $this->app->singleton(Registry::class, static fn () => new Registry($driver));
    }

    protected function tearDown(): void
    {
        \assert($this->app !== null);
        /** @var FilesystemManager $fs */
        $fs = $this->app->make(FilesystemManager::class);
        $disk = $fs->disk('local');
        if ($disk->exists($this->outputRelPath)) {
            $disk->delete($this->outputRelPath);
        }

        parent::tearDown();
    }

    /** Run the export command and ensure the PendingCommand executes before returning. */
    private function runExportCommand(string ...$options): void
    {
        $args = [];
        foreach ($options as $opt) {
            [$key, $value] = \explode('=', $opt, 2);
            $args[$key] = $value;
        }

        $pending = $this->artisan('openapi:export', $args);
        \assert($pending instanceof PendingCommand);
        $pending->assertSuccessful();
    }

    #[Test]
    public function command_runs_without_error(): void
    {
        $this->runExportCommand();
    }

    #[Test]
    public function command_writes_output_file(): void
    {
        $this->runExportCommand();

        \assert($this->app !== null);
        /** @var FilesystemManager $fs */
        $fs = $this->app->make(FilesystemManager::class);
        $this->assertTrue($fs->disk('local')->exists($this->outputRelPath));
    }

    #[Test]
    public function command_output_is_valid_json(): void
    {
        $this->runExportCommand();

        \assert($this->app !== null);
        /** @var FilesystemManager $fs */
        $fs = $this->app->make(FilesystemManager::class);
        $content = $fs->disk('local')->get($this->outputRelPath);
        $this->assertIsString($content);

        $decoded = \json_decode($content, true);
        $this->assertIsArray($decoded);

        /** @var array<string, mixed> $decoded */
        $this->assertEquals('3.1.0', $decoded['openapi'] ?? null);
    }

    #[Test]
    public function command_respects_title_option(): void
    {
        $this->runExportCommand('--title=Custom Title');

        \assert($this->app !== null);
        /** @var FilesystemManager $fs */
        $fs = $this->app->make(FilesystemManager::class);
        $content = $fs->disk('local')->get($this->outputRelPath);
        $this->assertIsString($content);

        $decoded = \json_decode($content, true);
        $this->assertIsArray($decoded);

        /** @var array{info: array{title: string}} $decoded */
        $this->assertEquals('Custom Title', $decoded['info']['title']);
    }
}
