<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use DateTimeImmutable;

readonly class GroupChatMemberAdded implements GroupChatEvent {
    private string $id;
    private GroupChatId $aggregateId;
    private Member $member;
    private int $sequenceNumber;
    private UserAccountId $executorId;
    private DateTimeImmutable $occurredAt;
    public function __construct(
        string $id,
        GroupChatId $aggregateId,
        MemberId $memberId,
        UserAccountId $userAccountId,
        MemberRole $role,
        UserAccountId $executorId,
        int $sequenceNumber,
        DateTimeImmutable $occurredAt
    ) {
        $this->id = $id;
        $this->aggregateId = $aggregateId;
        $this->member = new Member($memberId, $userAccountId, $role);
        $this->sequenceNumber = $sequenceNumber;
        $this->executorId = $executorId;
        $this->occurredAt = $occurredAt;
    }

    public function getId(): string {
        return $this->id;
    }

    public function getTypeName(): string {
        return "GroupChatMemberAdded";
    }

    public function getSequenceNumber(): int {
        return $this->sequenceNumber;
    }

    public function isCreated(): bool {
        return true;
    }

    public function getOccurredAt(): DateTimeImmutable {
        return $this->occurredAt;
    }

    public function getAggregateId(): GroupChatId {
        return $this->aggregateId;
    }

    public function getMember(): Member {
        return $this->member;
    }

    public function getExecutorId(): UserAccountId {
        return $this->executorId;
    }

    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id,
            'typeName' => $this->getTypeName(),
            'aggregateId' => $this->aggregateId,
            'member' => $this->member,
            'sequenceNumber' => $this->sequenceNumber,
            'executorId' => $this->executorId,
            'occurredAt' => $this->occurredAt,
        ];
    }
}
