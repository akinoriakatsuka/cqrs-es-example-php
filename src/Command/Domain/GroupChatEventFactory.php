<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Ulid\Ulid;
use DateTimeImmutable;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;

final class GroupChatEventFactory {
    public static function ofCreated(GroupChatId $id, Models\GroupChatName $name): GroupChatCreated {
        $eventId = "group-chat-event-" . Ulid::generate();
        $sequenceNumber = 1;
        $occurredAt = new DateTimeImmutable('now');
        return new GroupChatCreated(
            $eventId,
            $id,
            $name,
            $sequenceNumber,
            $occurredAt
        );
    }

    public static function ofMemberAdded(
        GroupChatId     $id,
        MemberId $memberId,
        UserAccountId $userAccountId,
        MemberRole $role,
        int $sequenceNumber,
        UserAccountId $executorId
    ): GroupChatMemberAdded {
        $eventId = "group-chat-event-" . Ulid::generate();
        $occurredAt = new DateTimeImmutable('now');
        return new GroupChatMemberAdded(
            $eventId,
            $id,
            $memberId,
            $userAccountId,
            $role,
            $executorId,
            $sequenceNumber,
            $occurredAt
        );
    }

    public static function ofDeleted(
        GroupChatId $id,
        int $sequenceNumber,
        UserAccountId $executorId
    ): GroupChatDeleted {
        $eventId = "group-chat-event-" . Ulid::generate();
        $occurredAt = new DateTimeImmutable('now');
        return new GroupChatDeleted(
            $eventId,
            $id,
            $executorId,
            $sequenceNumber,
            $occurredAt
        );
    }
}
