<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

final readonly class GroupChatMemberRemovedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {
    }

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatMemberRemovedEventHandler: start', ['event' => $event]);

        $groupChatId = $event['aggregate_id']['value'];
        $userAccountId = $event['user_account_id']['value'];

        $this->dao->removeMember($groupChatId, $userAccountId);

        $this->logger->info('GroupChatMemberRemovedEventHandler: finished');
    }
}
