<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class CreateGroupChatPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'CreateGroupChatPayload',
            'description' => 'Payload for createGroupChat mutation',
            'fields' => [
                'groupChat' => [
                    'type' => Type::nonNull(TypeRegistry::groupChatType()),
                    'description' => 'The created group chat',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}
