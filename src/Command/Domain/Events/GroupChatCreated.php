<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use DateTimeImmutable;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;

readonly class GroupChatCreated implements GroupChatEvent {
    private string $typeName;
    private string $id;
    private GroupChatId $aggregateId;
    private GroupChatName $name;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    public function __construct(
        string $id,
        GroupChatId $aggregateId,
        GroupChatName $name,
        int $sequenceNumber,
        DateTimeImmutable $occurredAt
    ) {
        $this->typeName = "GroupChatCreated";
        $this->id = $id;
        $this->aggregateId = $aggregateId;
        $this->name = $name;
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
     * Returns the name.
     *
     * @return GroupChatName
     */
    public function getName(): GroupChatName {
        return $this->name;
    }

    /**
     * Returns the sequence number.
     *
     * @return int
     */
    public function getSequenceNumber(): int {
        return 0;
    }

    /**
     * Determines whether it is a generated event.
     *
     * @return bool
     */
    public function isCreated(): bool {
        return true;
    }

    /**
     * Returns the occurred at.
     *
     * @return DateTimeImmutable
     */
    public function getOccurredAt(): DateTimeImmutable {
        return new DateTimeImmutable();
    }

    public function jsonSerialize(): mixed {
        return [
            "typeName" => $this->typeName,
            "id" => $this->id,
            "aggregateId" => $this->aggregateId,
            "sequenceNumber" => $this->sequenceNumber,
            "name" => $this->name,
            "occurredAt" => $this->occurredAt->getTimestamp() * 1000,
        ];
    }
}
