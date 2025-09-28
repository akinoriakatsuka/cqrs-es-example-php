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
