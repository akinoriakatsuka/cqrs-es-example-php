<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class GroupChatType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'GroupChat',
            'description' => 'A group chat entity',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The unique identifier of the group chat',
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The name of the group chat',
                ],
                'version' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'The version of the group chat',
                ],
                'isDeleted' => [
                    'type' => Type::nonNull(Type::boolean()),
                    'description' => 'Whether the group chat is deleted',
                ],
            ],
        ]);
    }
}
