<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;

final readonly class GroupChatMemberRemovedFactory
{
    public function __construct(
        private GroupChatIdFactory $groupChatIdFactory,
        private UserAccountIdFactory $userAccountIdFactory
    ) {
    }

    public function fromArray(array $data): GroupChatMemberRemoved
    {
        return new GroupChatMemberRemoved(
            $data['id'],
            $this->groupChatIdFactory->fromArray($data['aggregate_id']),
            $this->userAccountIdFactory->fromArray($data['user_account_id']),
            $data['seq_nr'],
            $this->userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
