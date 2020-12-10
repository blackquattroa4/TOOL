<?php

namespace App\GraphQL\Queries;

use App\TaxableEntity;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class TaxableEntitiesQuery extends Query
{
    protected $attributes = [
        'name' => 'taxable_entities',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('TaxableEntity'));
    }

    public function resolve($root, $args)
    {
        return TaxableEntity::all();
    }
}