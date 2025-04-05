<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use DateTimeImmutable;

class GroupChatMessagePosted implements GroupChatEvent
 {

    public function __construct()
    {
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        // TODO: Implement getId() method.
    }

    /**
     * @return string
     */
    public function getTypeName(): string
    {
        // TODO: Implement getTypeName() method.
    }

    /**
     * @return int
     */
    public function getSequenceNumber(): int
    {
        // TODO: Implement getSequenceNumber() method.
    }

    /**
     * @return bool
     */
    public function isCreated(): bool
    {
        // TODO: Implement isCreated() method.
    }

    /**
     * @return DateTimeImmutable
     */
    public function getOccurredAt(): DateTimeImmutable
    {
        // TODO: Implement getOccurredAt() method.
    }

    /**
     * @return GroupChatId
     */
    public function getAggregateId(): GroupChatId
    {
        // TODO: Implement getAggregateId() method.
    }

    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        // TODO: Implement jsonSerialize() method.
    }
}