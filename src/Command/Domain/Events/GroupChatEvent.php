<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatEventVisitor;

interface GroupChatEvent
{
    public function getId(): string;

    public function getTypeName(): string;

    public function getAggregateId(): string;

    public function getSeqNr(): int;

    public function getOccurredAt(): int;

    public function isCreated(): bool;

    public function toArray(): array;

    /**
     * Accept a visitor to apply this event to an aggregate
     *
     * @param GroupChatEventVisitor $visitor
     * @param GroupChat             $aggregate
     *
     * @return GroupChat
     */
    public function accept(GroupChatEventVisitor $visitor, GroupChat $aggregate): GroupChat;
}
