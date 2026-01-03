<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

final readonly class GroupChatRenamedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {
    }

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatRenamedEventHandler: start', ['event' => $event]);

        $groupChatId = $event['aggregate_id']['value'];
        $name = $event['name']['value'];

        $this->dao->renameGroupChat($groupChatId, $name);

        $this->logger->info('GroupChatRenamedEventHandler: finished');
    }
}
