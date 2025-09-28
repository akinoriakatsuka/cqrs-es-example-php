<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class DeleteGroupChatInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'DeleteGroupChatInput',
            'description' => 'Input for deleting a group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user deleting the group chat',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the group chat to delete',
                ],
            ],
        ]);
    }
}
