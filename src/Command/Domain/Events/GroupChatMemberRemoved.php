<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

final readonly class GroupChatMemberRemoved implements GroupChatEvent
{
    public function __construct(
        private string $id,
        private GroupChatId $aggregate_id,
        private UserAccountId $user_account_id,
        private int $seq_nr,
        private UserAccountId $executor_id,
        private int $occurred_at
    ) {
    }

    public static function create(
        GroupChatId $aggregate_id,
        UserAccountId $user_account_id,
        int $seq_nr,
        UserAccountId $executor_id
    ): self {
        $ulid = \Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid::generate();
        $id = $ulid->toString();
        $occurred_at = (int)(microtime(true) * 1000);
        return new self($id, $aggregate_id, $user_account_id, $seq_nr, $executor_id, $occurred_at);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTypeName(): string
    {
        return 'GroupChatMemberRemoved';
    }

    public function getAggregateId(): string
    {
        return $this->aggregate_id->toString();
    }

    public function getSeqNr(): int
    {
        return $this->seq_nr;
    }

    public function getUserAccountId(): UserAccountId
    {
        return $this->user_account_id;
    }

    public function getOccurredAt(): int
    {
        return $this->occurred_at;
    }

    public function isCreated(): bool
    {
        return false;
    }

    public function toArray(): array
    {
        return [
            'type_name' => $this->getTypeName(),
            'id' => $this->id,
            'aggregate_id' => $this->aggregate_id->toArray(),
            'user_account_id' => $this->user_account_id->toArray(),
            'executor_id' => $this->executor_id->toArray(),
            'seq_nr' => $this->seq_nr,
            'occurred_at' => $this->occurred_at,
        ];
    }


    public static function fromArrayWithFactories(
        array $data,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory $groupChatIdFactory,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory $userAccountIdFactory
    ): self {
        return new self(
            $data['id'],
            $groupChatIdFactory->fromArray($data['aggregate_id']),
            $userAccountIdFactory->fromArray($data['user_account_id']),
            $data['seq_nr'],
            $userAccountIdFactory->fromArray($data['executor_id']),
            $data['occurred_at']
        );
    }
}
