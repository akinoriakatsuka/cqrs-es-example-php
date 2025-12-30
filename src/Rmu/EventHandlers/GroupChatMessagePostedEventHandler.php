<?php

declare(strict_types=1);

namespace App\Rmu\EventHandlers;

use App\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

class GroupChatMessagePostedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {}

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatMessagePostedEventHandler: start', ['event' => $event]);

        $groupChatId = $event['aggregate_id']['value'];
        $message = $event['message'];
        $messageId = $message['id']['value'];
        $userAccountId = $message['sender_id']['value'];
        $text = $message['text'];

        $occurredAtMs = $event['occurred_at'];
        $occurredAt = $this->convertToDateTime($occurredAtMs);

        $this->dao->postMessage($messageId, $groupChatId, $userAccountId, $text, $occurredAt);

        $this->logger->info('GroupChatMessagePostedEventHandler: finished');
    }

    private function convertToDateTime(int $milliseconds): string
    {
        $seconds = intdiv($milliseconds, 1000);
        $dateTime = new \DateTime('@' . $seconds);
        return $dateTime->format('Y-m-d H:i:s');
    }
}
