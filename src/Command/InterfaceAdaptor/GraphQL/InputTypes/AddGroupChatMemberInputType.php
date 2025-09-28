<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\MemberRoleType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class AddGroupChatMemberInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'AddGroupChatMemberInput',
            'description' => 'Input for adding a member to a group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user adding the member',
                ],
                'groupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the group chat',
                ],
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The user account ID to add',
                ],
                'role' => [
                    'type' => Type::nonNull(new MemberRoleType()),
                    'description' => 'The role for the new member',
                ],
            ],
        ]);
    }
}
