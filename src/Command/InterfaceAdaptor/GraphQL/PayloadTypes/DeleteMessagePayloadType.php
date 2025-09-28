<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

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
