<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use J5ik2o\EventStoreAdapterPhp\AggregateId;
use J5ik2o\EventStoreAdapterPhp\Event;

class GroupChatEventAdapter implements Event
{
    public function __construct(
        private GroupChatEvent $event,
        private UlidValidator $validator
    ) {
    }

    public function getId(): string
    {
        return $this->event->getId();
    }

    public function getTypeName(): string
    {
        return $this->event->getTypeName();
    }

    public function getAggregateId(): AggregateId
    {
        $group_chat_id_str = $this->event->getAggregateId();
        $group_chat_id = GroupChatId::fromString($group_chat_id_str, $this->validator);
        return new GroupChatIdAdapter($group_chat_id);
    }

    public function getSequenceNumber(): int
    {
        return $this->event->getSeqNr();
    }

    public function isCreated(): bool
    {
        return $this->event->isCreated();
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        $timestamp_ms = $this->event->getOccurredAt();
        $timestamp_sec = (int)($timestamp_ms / 1000);
        $microseconds = ($timestamp_ms % 1000) * 1000;
        return \DateTimeImmutable::createFromFormat('U', (string)$timestamp_sec)
            ->modify("+{$microseconds} microseconds");
    }

    public function jsonSerialize(): mixed
    {
        return $this->event->toArray();
    }

    public function getEvent(): GroupChatEvent
    {
        return $this->event;
    }
}
