<?php

declare(strict_types=1);

use DanDoeTech\ResourceRegistry\Definition\FieldDefinition;
use DanDoeTech\ResourceRegistry\Definition\FieldType;
use DanDoeTech\ResourceRegistry\Definition\RelationDefinition;
use DanDoeTech\ResourceRegistry\Definition\RelationType;

return [
    'product' => [
        'label' => 'Product',
        'fields' => [
            new FieldDefinition('id', FieldType::Integer, false),
            new FieldDefinition('name', FieldType::String, false, ['required', 'max:120']),
            new FieldDefinition('price', FieldType::Float, false, ['min:0']),
            new FieldDefinition('created_at', FieldType::DateTime, false),
        ],
        'relations' => [
            new RelationDefinition('category', RelationType::BelongsTo, 'category'),
        ],
        'actions' => [
            ['name' => 'create'], ['name' => 'update'], ['name' => 'delete'],
        ],
    ],
    'category' => [
        'label' => 'Category',
        'fields' => [
            new FieldDefinition('id', FieldType::Integer, false),
            new FieldDefinition('name', FieldType::String, false, ['required', 'max:80']),
        ],
        'relations' => [
            new RelationDefinition('products', RelationType::HasMany, 'product'),
        ],
        'actions' => [
            ['name' => 'create'],
        ],
    ],
];
