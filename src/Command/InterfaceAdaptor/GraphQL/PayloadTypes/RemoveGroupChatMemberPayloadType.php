<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

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
