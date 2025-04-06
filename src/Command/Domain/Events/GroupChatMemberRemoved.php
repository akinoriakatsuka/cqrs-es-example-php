<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use DateTimeImmutable;

readonly class GroupChatMemberRemoved implements GroupChatEvent {
    private string $id;
    private GroupChatId $aggregateId;
    private UserAccountId $memberUserAccountId;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        string $id,
        GroupChatId $aggregateId,
        UserAccountId $memberUserAccountId,
        UserAccountId $executorId,
        int $sequenceNumber,
        DateTimeImmutable $occurredAt
    ) {
        $this->id = $id;
        $this->aggregateId = $aggregateId;
        $this->memberUserAccountId = $memberUserAccountId;
        $this->executorId = $executorId;
        $this->sequenceNumber = $sequenceNumber;
        $this->occurredAt = $occurredAt;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getTypeName(): string {
        return 'group-chat-member-removed';
    }

    public function getSequenceNumber(): int {
        return $this->sequenceNumber;
    }

    public function isCreated(): bool {
        return false;
    }

    public function getOccurredAt(): DateTimeImmutable {
        return $this->occurredAt;
    }

    public function getAggregateId(): GroupChatId {
        return $this->aggregateId;
    }

    public function getMemberUserAccountId(): UserAccountId {
        return $this->memberUserAccountId;
    }

    public function getExecutorId(): UserAccountId {
        return $this->executorId;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'type' => $this->getTypeName(),
            'groupChatId' => $this->aggregateId,
            'memberUserAccountId' => $this->memberUserAccountId,
            'executorId' => $this->executorId,
            'sequenceNumber' => $this->sequenceNumber,
            'occurredAt' => $this->occurredAt->format(DATE_ATOM),
        ];
    }
}
