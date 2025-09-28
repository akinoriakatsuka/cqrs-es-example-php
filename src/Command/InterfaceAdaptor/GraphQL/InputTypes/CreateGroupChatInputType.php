<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\InputTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\MemberRoleType;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

final class CreateGroupChatInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'CreateGroupChatInput',
            'description' => 'Input for creating a new group chat',
            'fields' => [
                'executorId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user creating the group chat',
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The name of the group chat',
                ],
            ],
        ]);
    }
}

final class AddMemberInputType extends InputObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'AddMemberInput',
            'description' => 'Input for adding a member to a group chat',
            'fields' => [
                'userAccountId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The user account ID to add',
                ],
                'role' => [
                    'type' => Type::nonNull(new MemberRoleType()),
                    'description' => 'The role for the member',
                ],
            ],
        ]);
    }
}
