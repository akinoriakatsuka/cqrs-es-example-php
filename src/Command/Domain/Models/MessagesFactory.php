<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

final readonly class MessagesFactory
{
    public function __construct(
        private MessageFactory $messageFactory
    ) {
    }

    public function fromArray(array $data): Messages
    {
        $values = $data['values'] ?? [];
        $messages = array_map(
            fn ($message_data) => $this->messageFactory->fromArray($message_data),
            $values
        );
        return Messages::fromMessages($messages);
    }
}
