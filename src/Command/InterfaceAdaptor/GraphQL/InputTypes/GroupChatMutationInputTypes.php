<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\MemberRoleType;
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
