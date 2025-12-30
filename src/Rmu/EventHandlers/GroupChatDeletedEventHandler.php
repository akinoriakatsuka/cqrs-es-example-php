<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

class GroupChatDeletedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {
    }

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatDeletedEventHandler: start', ['event' => $event]);

        $groupChatId = $event['aggregate_id']['value'];

        $this->dao->deleteGroupChat($groupChatId);

        $this->logger->info('GroupChatDeletedEventHandler: finished');
    }
}
