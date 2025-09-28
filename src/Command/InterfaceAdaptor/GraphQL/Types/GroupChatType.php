<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class GroupChatType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'GroupChat',
            'description' => 'A group chat',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The unique identifier of the group chat',
                    'resolve' => static fn (GroupChat $group_chat): string => $group_chat->getId()->getValue(),
                ],
                'name' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The name of the group chat',
                    'resolve' => static fn (GroupChat $group_chat): string => $group_chat->getName()->getValue(),
                ],
                'membersCount' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'Number of members in the group chat',
                    'resolve' => static fn (GroupChat $group_chat): int => count($group_chat->getMembers()->getValues()),
                ],
                'messagesCount' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'Number of messages in the group chat',
                    'resolve' => static fn (GroupChat $group_chat): int => count($group_chat->getMessages()->getValues()),
                ],
                'version' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'Version of the group chat',
                    'resolve' => static fn (GroupChat $group_chat): int => $group_chat->getVersion(),
                ],
                'sequenceNumber' => [
                    'type' => Type::nonNull(Type::int()),
                    'description' => 'Sequence number of the group chat',
                    'resolve' => static fn (GroupChat $group_chat): int => $group_chat->getSequenceNumber(),
                ],
            ],
        ]);
    }
}
