<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

interface GroupChatEvent
{
    public function getId(): string;

    public function getTypeName(): string;

    public function getAggregateId(): string;

    public function getSeqNr(): int;

    public function getOccurredAt(): int;

    public function isCreated(): bool;

    public function toArray(): array;
}
