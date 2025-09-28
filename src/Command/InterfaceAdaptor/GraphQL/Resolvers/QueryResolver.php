<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class QueryResolver extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'Query',
            'description' => 'Root query type',
            'fields' => [
                'hello' => [
                    'type' => Type::string(),
                    'description' => 'A simple hello query for testing',
                    'resolve' => static fn (): string => 'Hello, GraphQL from PHP!',
                ],
                'version' => [
                    'type' => Type::string(),
                    'description' => 'API version',
                    'resolve' => static fn (): string => '1.0.0',
                ],
            ],
        ]);
    }
}
