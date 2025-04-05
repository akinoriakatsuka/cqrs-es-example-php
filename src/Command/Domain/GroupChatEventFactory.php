<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Ulid\Ulid;
use DateTimeImmutable;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;

final class GroupChatEventFactory {
    public static function ofCreated(GroupChatId $id, GroupChatName $name): GroupChatCreated {
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

    public static function ofRenamed(
        GroupChatId $id,
        GroupChatName $name,
        int $sequenceNumber,
        UserAccountId $executorId
    ): GroupChatRenamed {
        $eventId = "group-chat-event-" . Ulid::generate();
        $occurredAt = new DateTimeImmutable('now');
        return new GroupChatRenamed(
            $eventId,
            $id,
            $name,
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

    public static function ofMessagePosted(
        GroupChatId $id,
        Message $message,
        UserAccountId $executorId,
        int $sequenceNumber
    ): GroupChatMessagePosted {
        $eventId = "group-chat-event-" . Ulid::generate();
        $occurredAt = new DateTimeImmutable('now');
        return new GroupChatMessagePosted(
            $eventId,
            $id,
            $message,
            $executorId,
            $sequenceNumber,
            $occurredAt
        );
    }
}
