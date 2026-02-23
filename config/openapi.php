<?php

declare(strict_types=1);

return [
    'title'   => env('OPENAPI_TITLE', 'API'),
    'version' => env('OPENAPI_VERSION', '1.0.0'),
    // Relative to storage_path(); default: storage/app/openapi.json
    'output'  => env('OPENAPI_OUTPUT', 'app/openapi.json'),
    // If you do not yet have a Laravel bridge for the registry, point to a config file:
    'resources_config' => base_path('config/resources.php'),
];
