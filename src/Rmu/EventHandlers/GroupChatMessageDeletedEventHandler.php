<?php

declare(strict_types=1);

namespace App\Rmu\EventHandlers;

use App\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

class GroupChatMessageDeletedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {}

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatMessageDeletedEventHandler: start', ['event' => $event]);

        $messageId = $event['message_id']['value'];

        $this->dao->deleteMessage($messageId);

        $this->logger->info('GroupChatMessageDeletedEventHandler: finished');
    }
}
