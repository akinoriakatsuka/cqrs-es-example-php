<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\UserAccountIdFactory;

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
