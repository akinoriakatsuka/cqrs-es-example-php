<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class EditMessageInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'EditMessageInput',
            'description' => 'Input for editing a message in a group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user editing the message',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the group chat',
                ],
                'messageId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the message to edit',
                ],
                'content' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The new content of the message',
                ],
            ],
        ]);
    }
}
