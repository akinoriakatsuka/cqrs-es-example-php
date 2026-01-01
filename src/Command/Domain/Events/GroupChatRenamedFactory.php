<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;

final readonly class GroupChatRenamedFactory
{
    public function __construct(
        private GroupChatIdFactory $groupChatIdFactory,
        private UserAccountIdFactory $userAccountIdFactory
    ) {
    }

    public function fromArray(array $data): GroupChatRenamed
    {
        return new GroupChatRenamed(
            $data['id'],
            $this->groupChatIdFactory->fromArray($data['aggregate_id']),
            GroupChatName::fromArray($data['name']),
            $data['seq_nr'],
            $this->userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
