<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Events;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatMemberAddedTest extends TestCase {
    private GroupChatMemberAdded $event;
    private string $id;
    private GroupChatId $aggregateId;
    private MemberId $memberId;
    private UserAccountId $userAccountId;
    private MemberRole $role;
    private UserAccountId $executorId;
    private int $sequenceNumber;
    private DateTimeImmutable $occurredAt;

    protected function setUp(): void {
        $this->id = 'event-id-123';
        $this->aggregateId = new GroupChatId();
        $this->memberId = new MemberId();
        $this->userAccountId = new UserAccountId();
        $this->role = MemberRole::ADMIN_ROLE;
        $this->executorId = new UserAccountId();
        $this->sequenceNumber = 4;
        $this->occurredAt = new DateTimeImmutable();

        $this->event = new GroupChatMemberAdded(
            $this->id,
            $this->aggregateId,
            $this->memberId,
            $this->userAccountId,
            $this->role,
            $this->executorId,
            $this->sequenceNumber,
            $this->occurredAt
        );
    }

    public function testGetId(): void {
        $this->assertSame($this->id, $this->event->getId());
    }

    public function testGetTypeName(): void {
        $this->assertSame('GroupChatMemberAdded', $this->event->getTypeName());
    }

    public function testGetAggregateId(): void {
        $this->assertSame($this->aggregateId, $this->event->getAggregateId());
    }

    public function testGetMember(): void {
        $member = $this->event->getMember();
        $this->assertSame($this->role, $member->getRole());
    }

    public function testGetExecutorId(): void {
        $this->assertSame($this->executorId, $this->event->getExecutorId());
    }

    public function testGetSequenceNumber(): void {
        $this->assertSame($this->sequenceNumber, $this->event->getSequenceNumber());
    }

    public function testIsCreated(): void {
        $this->assertFalse($this->event->isCreated());
    }

    public function testGetOccurredAt(): void {
        $this->assertSame($this->occurredAt, $this->event->getOccurredAt());
    }

    public function testJsonSerialize(): void {
        $json = $this->event->jsonSerialize();

        $this->assertIsArray($json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('typeName', $json);
        $this->assertArrayHasKey('aggregateId', $json);
        $this->assertArrayHasKey('member', $json);
        $this->assertArrayHasKey('executorId', $json);
        $this->assertArrayHasKey('sequenceNumber', $json);
        $this->assertArrayHasKey('occurredAt', $json);

        $this->assertSame($this->id, $json['id']);
        $this->assertSame('GroupChatMemberAdded', $json['typeName']);
        $this->assertSame($this->aggregateId, $json['aggregateId']);
        $this->assertSame($this->event->getMember(), $json['member']);
        $this->assertSame($this->executorId, $json['executorId']);
        $this->assertSame($this->sequenceNumber, $json['sequenceNumber']);
    }
}
