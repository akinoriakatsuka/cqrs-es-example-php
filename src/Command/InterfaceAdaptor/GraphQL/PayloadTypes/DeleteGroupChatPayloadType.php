<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

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
