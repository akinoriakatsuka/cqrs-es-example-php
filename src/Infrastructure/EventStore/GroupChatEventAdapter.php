<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use J5ik2o\EventStoreAdapterPhp\AggregateId;
use J5ik2o\EventStoreAdapterPhp\Event;

class GroupChatEventAdapter implements Event
{
    private GroupChatIdFactory $group_chat_id_factory;

    public function __construct(
        private GroupChatEvent $event,
        UlidValidator $validator
    ) {
        $this->group_chat_id_factory = new GroupChatIdFactory(
            new RobinvdvleutenUlidGenerator(),
            $validator
        );
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
        $group_chat_id = $this->group_chat_id_factory->fromString($group_chat_id_str);
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
