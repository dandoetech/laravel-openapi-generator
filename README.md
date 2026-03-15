# Laravel OpenAPI Generator

Laravel bridge for the DanDoeTech OpenAPI generator. Provides an Artisan command to export OpenAPI 3.1 specs from the Resource Registry.

## Installation

```bash
composer require dandoetech/laravel-openapi-generator
```

The service provider is auto-discovered. Publish the config:

```bash
php artisan vendor:publish --tag=ddt-openapi-config
```

Requires [`dandoetech/laravel-resource-registry`](https://github.com/dandoetech/laravel-resource-registry).

## Quick Start

Export the spec:

```bash
php artisan openapi:export
```

This generates an OpenAPI 3.1 JSON file at `storage/app/openapi.json` (configurable).

Override title or version:

```bash
php artisan openapi:export --title="Catalog API" --oaversion="2.0.0"
php artisan openapi:export --output="public/openapi.json"
```

### What Gets Generated

Every registered resource produces:
- `GET /{resource}` — list endpoint
- `POST /{resource}` — create endpoint
- `GET /{resource}/{id}` — show endpoint
- A component schema with all fields and computed fields

Example output (abbreviated):

```json
{
  "openapi": "3.1.0",
  "info": { "title": "API", "version": "1.0.0" },
  "servers": [{ "url": "http://localhost/api", "description": "Primary API" }],
  "paths": {
    "/product": {
      "get": { "summary": "List Product" },
      "post": { "summary": "Create Product" }
    },
    "/product/{id}": {
      "get": { "summary": "Fetch Product" }
    }
  },
  "components": {
    "schemas": {
      "Product": {
        "type": "object",
        "properties": {
          "name": { "type": "string" },
          "price": { "type": "number", "format": "double" },
          "category_name": { "type": "string" }
        },
        "required": ["name", "price"]
      },
      "ProblemJson": { "..." : "..." }
    }
  }
}
```

Computed fields (like `category_name`) appear in schemas as regular properties. Error responses use the RFC 7807 `ProblemJson` schema.

## Configuration

`config/ddt_openapi.php`:

```php
return [
    // OpenAPI info block
    'title'   => env('OPENAPI_TITLE', 'API'),
    'version' => env('OPENAPI_VERSION', '1.0.0'),

    // Output path relative to storage_path()
    'output'  => env('OPENAPI_OUTPUT', 'app/openapi.json'),
];
```

The server URL is built from `config('app.url')` and the API prefix from `config('ddt_api.prefix')` (configured in [`laravel-generic-api`](https://github.com/dandoetech/laravel-generic-api)).

## Programmatic Use

Generate the spec in code:

```php
use DanDoeTech\LaravelOpenApiGenerator\Services\OpenApiGenerator;

$generator = app(OpenApiGenerator::class);
$spec = $generator->create(); // array

return response()->json($spec);
```

## API Overview

| Class | Purpose |
|---|---|
| `OpenApiServiceProvider` | Registers config, artisan command |
| `OpenApiExportCommand` | `openapi:export` with `--output`, `--title`, `--oaversion` options |
| `OpenApiGenerator` (service) | Builds spec from Registry + config, returns array |

## Testing

```bash
composer install
composer test        # PHPUnit (Orchestra Testbench)
composer qa          # cs:check + phpstan + test
```

## License

MIT
