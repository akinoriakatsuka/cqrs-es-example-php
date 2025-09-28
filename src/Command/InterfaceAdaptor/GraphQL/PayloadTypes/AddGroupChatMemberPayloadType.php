<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\GroupChatType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

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
