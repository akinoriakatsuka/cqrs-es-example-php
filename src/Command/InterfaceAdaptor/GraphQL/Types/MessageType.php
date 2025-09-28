<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

final class MessageType extends ObjectType {
    public function __construct() {
        parent::__construct([
            'name' => 'Message',
            'description' => 'A message in a group chat',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The unique identifier of the message',
                    'resolve' => static fn (Message $message): string => $message->getId()->getValue(),
                ],
                'content' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'The content of the message',
                    'resolve' => static fn (Message $message): string => $message->getContent(),
                ],
                'senderId' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The ID of the user who sent the message',
                    'resolve' => static fn (Message $message): string => $message->getSenderId()->getValue(),
                ],
                'sentAt' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'When the message was sent',
                    'resolve' => static fn (Message $message): string => $message->getSentAt()->format('c'),
                ],
                'editedAt' => [
                    'type' => Type::string(),
                    'description' => 'When the message was last edited',
                    'resolve' => static fn (Message $message): ?string => $message->getEditedAt()?->format('c'),
                ],
            ],
        ]);
    }
}
