<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class CreateGroupChatPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'CreateGroupChatPayload',
            'description' => 'Payload for createGroupChat mutation',
            'fields' => [
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The created group chat',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}

final class DeleteGroupChatPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'DeleteGroupChatPayload',
            'description' => 'Payload for deleteGroupChat mutation',
            'fields' => [
                'deletedGroupChatId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the deleted group chat',
                    'resolve' => static fn (array $payload): string => $payload['deletedGroupChatId'],
                ],
            ],
        ]);
    }
}

final class RenameGroupChatPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'RenameGroupChatPayload',
            'description' => 'Payload for renameGroupChat mutation',
            'fields' => [
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The renamed group chat',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}

final class AddGroupChatMemberPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'AddGroupChatMemberPayload',
            'description' => 'Payload for addMember mutation',
            'fields' => [
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The group chat with the added member',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}

final class RemoveGroupChatMemberPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'RemoveGroupChatMemberPayload',
            'description' => 'Payload for removeMember mutation',
            'fields' => [
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The group chat with the removed member',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}
