<?php

namespace App\GraphQL\Types;

use App\TaxableEntity;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class TaxableEntityType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TaxableEntity',
        'description' => 'Details about a taxable entity',
        'model' => TaxableEntity::class
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'Id of the entity',
            ],
            'code' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The code of the entity',
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The name of the entity',
            ],
            'tax_id' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The tax id of the entity',
            ]
        ];
    }
}
