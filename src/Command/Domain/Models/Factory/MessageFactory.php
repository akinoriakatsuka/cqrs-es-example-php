<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;

final readonly class MessageFactory
{
    public function __construct(
        private UserAccountIdFactory $userAccountIdFactory,
        private MessageIdFactory $messageIdFactory
    ) {
    }

    public function fromArray(array $data): Message
    {
        return new Message(
            $this->messageIdFactory->fromArray($data['id'] ?? []),
            $data['text'] ?? '',
            $this->userAccountIdFactory->fromArray($data['sender_id'] ?? [])
        );
    }
}
