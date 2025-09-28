<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\MessageType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

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
