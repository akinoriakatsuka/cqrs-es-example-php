<?php

declare(strict_types=1);

namespace App\Rmu\EventHandlers;

use App\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

class GroupChatMessageEditedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {
    }

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatMessageEditedEventHandler: start', ['event' => $event]);

        $message = $event['message'];
        $messageId = $message['id']['value'];
        $text = $message['text'];

        $this->dao->editMessage($messageId, $text);

        $this->logger->info('GroupChatMessageEditedEventHandler: finished');
    }
}
