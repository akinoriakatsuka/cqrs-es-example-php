<?php

declare(strict_types=1);

namespace App\Rmu\EventHandlers;

use App\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

class GroupChatMemberAddedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {
    }

    public function handle(array $event): void
    {
        $this->logger->info('GroupChatMemberAddedEventHandler: start', ['event' => $event]);

        $groupChatId = $event['aggregate_id']['value'];
        $member = $event['member'];
        $memberId = $member['id']['value'];
        $userAccountId = $member['user_account_id']['value'];
        $role = $this->convertRole($member['role']);

        $occurredAtMs = $event['occurred_at'];
        $occurredAt = $this->convertToDateTime($occurredAtMs);

        $this->dao->addMember($memberId, $groupChatId, $userAccountId, $role, $occurredAt);

        $this->logger->info('GroupChatMemberAddedEventHandler: finished');
    }

    private function convertToDateTime(int $milliseconds): string
    {
        $seconds = intdiv($milliseconds, 1000);
        $dateTime = new \DateTime('@' . $seconds);
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function convertRole(int $roleValue): string
    {
        return match ($roleValue) {
            0 => 'member',
            1 => 'admin',
            default => throw new \InvalidArgumentException("Invalid role value: {$roleValue}"),
        };
    }
}
