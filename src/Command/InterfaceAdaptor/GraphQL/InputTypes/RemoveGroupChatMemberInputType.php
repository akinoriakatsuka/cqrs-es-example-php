<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class RemoveGroupChatMemberInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'RemoveGroupChatMemberInput',
            'description' => 'Input for removing a member from a group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user removing the member',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the group chat',
                ],
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The user account ID to remove',
                ],
            ],
        ]);
    }
}
