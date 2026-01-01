<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;

final readonly class GroupChatMemberAddedFactory
{
    public function __construct(
        private GroupChatIdFactory $groupChatIdFactory,
        private UserAccountIdFactory $userAccountIdFactory,
        private MemberFactory $memberFactory
    ) {
    }

    public function fromArray(array $data): GroupChatMemberAdded
    {
        return new GroupChatMemberAdded(
            $data['id'],
            $this->groupChatIdFactory->fromArray($data['aggregate_id']),
            $this->memberFactory->fromArray($data['member']),
            $data['seq_nr'],
            $this->userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
