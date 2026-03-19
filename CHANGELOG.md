# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

_No functional changes since v0.1.0 — dependency updates only._

## [0.1.0] - 2026-03-15

### Added
- `openapi:export` artisan command with `--output`, `--title`, `--oaversion` options
- `OpenApiGenerator` service class for programmatic spec generation
- `OpenApiServiceProvider` registering config and artisan command
- `ddt_openapi.php` config with title, version, and output path (env-overridable)
- Server URL built from `app.url` and `ddt_api.prefix` config
- Registry integration via shared singleton binding
- Optional `ModelMetaProviderInterface` fallback for enhanced field metadata
- Computed fields included in generated schemas
