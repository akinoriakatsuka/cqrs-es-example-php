<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class PostMessageInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'PostMessageInput',
            'description' => 'Input for posting a message to a group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user posting the message',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the group chat',
                ],
                'content' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The content of the message',
                ],
            ],
        ]);
    }
}
