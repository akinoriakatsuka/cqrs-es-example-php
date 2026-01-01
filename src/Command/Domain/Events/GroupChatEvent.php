<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;

interface GroupChatEvent
{
    public function getId(): string;

    public function getTypeName(): string;

    public function getAggregateId(): string;

    public function getSeqNr(): int;

    public function getOccurredAt(): int;

    public function isCreated(): bool;

    public function toArray(): array;

    public function applyTo(GroupChat $aggregate): GroupChat;
}
