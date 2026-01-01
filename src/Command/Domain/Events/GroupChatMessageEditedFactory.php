<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;

final readonly class GroupChatMessageEditedFactory
{
    public function __construct(
        private GroupChatIdFactory $groupChatIdFactory,
        private UserAccountIdFactory $userAccountIdFactory,
        private MessageFactory $messageFactory
    ) {
    }

    public function fromArray(array $data): GroupChatMessageEdited
    {
        return new GroupChatMessageEdited(
            $data['id'],
            $this->groupChatIdFactory->fromArray($data['aggregate_id']),
            $this->messageFactory->fromArray($data['message']),
            $data['seq_nr'],
            $this->userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
