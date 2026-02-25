<?php

declare(strict_types=1);

return [
    'title'   => env('OPENAPI_TITLE', 'API'),
    'version' => env('OPENAPI_VERSION', '1.0.0'),
    // Relative to storage_path(); default: storage/app/openapi.json
    'output'  => env('OPENAPI_OUTPUT', 'app/openapi.json'),
];
