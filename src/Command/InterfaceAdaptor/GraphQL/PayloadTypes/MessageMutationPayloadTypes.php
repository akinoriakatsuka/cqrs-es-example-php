<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\MessageType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class PostMessagePayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'PostMessagePayload',
            'description' => 'Payload for postMessage mutation',
            'fields' => [
                'message' => [
                    'type' => Type::nonNull(new MessageType()),
                    'description' => 'The posted message',
                    'resolve' => static fn (array $payload) => $payload['message'],
                ],
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The group chat containing the message',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}

final class EditMessagePayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'EditMessagePayload',
            'description' => 'Payload for editMessage mutation',
            'fields' => [
                'message' => [
                    'type' => Type::nonNull(new MessageType()),
                    'description' => 'The edited message',
                    'resolve' => static fn (array $payload) => $payload['message'],
                ],
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The group chat containing the message',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}

final class DeleteMessagePayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'DeleteMessagePayload',
            'description' => 'Payload for deleteMessage mutation',
            'fields' => [
                'deletedMessageId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the deleted message',
                    'resolve' => static fn (array $payload): string => $payload['deletedMessageId'],
                ],
                'groupChat' => [
                    'type' => Type::nonNull(new GroupChatType()),
                    'description' => 'The group chat after message deletion',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}
