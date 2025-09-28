<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class RenameGroupChatInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'RenameGroupChatInput',
            'description' => 'Input for renaming a group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user renaming the group chat',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the group chat to rename',
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The new name for the group chat',
                ],
            ],
        ]);
    }
}
