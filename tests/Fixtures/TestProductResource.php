<?php

declare(strict_types=1);

namespace DanDoeTech\LaravelOpenApiGenerator\Tests\Fixtures;

use DanDoeTech\ResourceRegistry\Builder\ResourceBuilder;
use DanDoeTech\ResourceRegistry\Definition\FieldType;
use DanDoeTech\ResourceRegistry\Resource;

final class TestProductResource extends Resource
{
    protected function define(ResourceBuilder $builder): void
    {
        $builder->key('product')
            ->version(1)
            ->label('Product')
            ->timestamps()
            ->field('name', FieldType::String, nullable: false, rules: ['required', 'max:120'])
            ->field('price', FieldType::Float, nullable: false, rules: ['required', 'numeric', 'min:0'])
            ->field('category_id', FieldType::Integer, nullable: false)
            ->belongsTo('category', target: 'category', foreignKey: 'category_id')
            ->computed('category_name', FieldType::String, via: 'category.name')
            ->filterable(['name', 'price', 'category_id'])
            ->sortable(['name', 'price', 'created_at'])
            ->action('create')
            ->action('update')
            ->action('delete');
    }
}
