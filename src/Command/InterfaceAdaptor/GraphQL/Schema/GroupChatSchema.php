<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Schema;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Resolvers\MutationResolver;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

class GroupChatSchema {
    private GroupChatType $group_chat_type;
    private MutationResolver $mutation_resolver;

    public function __construct(
        MutationResolver $mutation_resolver
    ) {
        $this->group_chat_type = new GroupChatType();
        $this->mutation_resolver = $mutation_resolver;
    }

    public function build(): Schema {
        $mutation_type = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'createGroupChat' => [
                    'type' => Type::nonNull($this->group_chat_type),
                    'args' => [
                        'name' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The name of the group chat',
                        ],
                        'executorId' => [
                            'type' => Type::nonNull(Type::string()),
                            'description' => 'The ID of the user creating the group chat',
                        ],
                    ],
                    'resolve' => [$this->mutation_resolver, 'createGroupChat'],
                ],
            ],
        ]);

        return new Schema([
            'mutation' => $mutation_type,
        ]);
    }
}
