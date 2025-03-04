<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use DateTimeImmutable;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

readonly class GroupChatDeleted implements GroupChatEvent {
    private string $typeName;
    private string $id;
    private GroupChatId $aggregateId;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        string $id,
        GroupChatId $aggregateId,
        UserAccountId $executorId,
        int $sequenceNumber,
        DateTimeImmutable $occurredAt
    ) {
        $this->typeName = "GroupChatDeleted";
        $this->id = $id;
        $this->aggregateId = $aggregateId;
        $this->executorId = $executorId;
        $this->sequenceNumber = $sequenceNumber;
        $this->occurredAt = $occurredAt;
    }

    /**
     * Returns the ID.
     *
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Returns the type name.
     *
     * @return string
     */
    public function getTypeName(): string {
        return $this->typeName;
    }

    /**
     * Returns the aggregate ID.
     *
     * @return GroupChatId
     */
    public function getAggregateId(): GroupChatId {
        return $this->aggregateId;
    }

    /**
     * Returns the executor ID.
     *
     * @return UserAccountId
     */
    public function getExecutorId(): UserAccountId {
        return $this->executorId;
    }

    /**
     * Returns the sequence number.
     *
     * @return int
     */
    public function getSequenceNumber(): int {
        return $this->sequenceNumber;
    }

    /**
     * Determines whether it is a generated event.
     *
     * @return bool
     */
    public function isCreated(): bool {
        return false;
    }

    /**
     * Returns the occurred at.
     *
     * @return DateTimeImmutable
     */
    public function getOccurredAt(): DateTimeImmutable {
        return $this->occurredAt;
    }

    public function jsonSerialize(): mixed {
        return [
            "typeName" => $this->typeName,
            "id" => $this->id,
            "aggregateId" => $this->aggregateId,
            "executorId" => $this->executorId,
            "sequenceNumber" => $this->sequenceNumber,
            "occurredAt" => $this->occurredAt->getTimestamp() * 1000,
        ];
    }
}
