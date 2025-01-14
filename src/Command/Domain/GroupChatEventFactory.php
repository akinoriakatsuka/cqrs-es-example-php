<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Ulid\Ulid;
use DateTimeImmutable;

final class GroupChatEventFactory {
    public static function ofCreated(GroupChatId $id, string $name): GroupChatCreated {
        $eventId = "group-chat-event-" . Ulid::generate();
        return new GroupChatCreated(
            $eventId,
            $id,
            1,
            $name,
            new DateTimeImmutable('now')
        );
    }
}
