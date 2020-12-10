<?php

namespace App\GraphQL\Queries;

use App\TaxableEntity;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;

class TaxableEntityQuery extends Query
{
    protected $attributes = [
        'name' => 'taxable_entity',
    ];

    public function type(): Type
    {
        return GraphQL::type('TaxableEntity');
    }

    public function args(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int(),
                'rules' => ['required']
            ],
        ];
    }

    public function resolve($root, $args)
    {
        return TaxableEntity::findOrFail($args['id']);
    }
}