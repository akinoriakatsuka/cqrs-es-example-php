<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\PayloadTypes;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types\TypeRegistry;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class RenameGroupChatPayloadType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'RenameGroupChatPayload',
            'description' => 'Payload for renameGroupChat mutation',
            'fields' => [
                'groupChat' => [
                    'type' => Type::nonNull(TypeRegistry::groupChatType()),
                    'description' => 'The renamed group chat',
                    'resolve' => static fn (array $payload) => $payload['groupChat'],
                ],
            ],
        ]);
    }
}
