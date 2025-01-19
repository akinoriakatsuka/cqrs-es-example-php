<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Ulid\Ulid;
use DateTimeImmutable;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;

final class GroupChatEventFactory {
    public static function ofCreated(GroupChatId $id, string $name): GroupChatCreated {
        $eventId = "group-chat-event-" . Ulid::generate();
        $sequenceNumber = 1;
        $occurredAt = new DateTimeImmutable('now');
        return new GroupChatCreated(
            $eventId,
            $id,
            $sequenceNumber,
            $name,
            $occurredAt
        );
    }
}
