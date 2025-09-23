<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatEventFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatEventFactoryTest extends TestCase {
    public function testOfCreated(): void {
        $id = new GroupChatId();
        $name = new GroupChatName('Test Chat');
        $event = GroupChatEventFactory::ofCreated($id, $name);
        $this->assertSame($id, $event->getAggregateId());
        $this->assertSame($name, $event->getName());
        $this->assertEquals(1, $event->getSequenceNumber());
        $this->assertNotEmpty($event->getOccurredAt());
    }

    public function testOfMemberAdded(): void {
        $id = new GroupChatId();
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();
        $role = MemberRole::fromString('admin');
        $sequenceNumber = 2;
        $executorId = new UserAccountId();

        $event = GroupChatEventFactory::ofMemberAdded($id, $memberId, $userAccountId, $role, $sequenceNumber, $executorId);
        $this->assertSame($id, $event->getAggregateId());
        $this->assertSame($executorId, $event->getExecutorId());
        $this->assertEquals($sequenceNumber, $event->getSequenceNumber());
        $this->assertNotEmpty($event->getOccurredAt());
    }

    public function testOfRenamed(): void {
        $id = new GroupChatId();
        $name = new GroupChatName('Renamed Chat');
        $sequenceNumber = 3;
        $executorId = new UserAccountId();

        $event = GroupChatEventFactory::ofRenamed($id, $name, $sequenceNumber, $executorId);
        $this->assertSame($id, $event->getAggregateId());
        $this->assertSame($name, $event->getName());
        $this->assertSame($executorId, $event->getExecutorId());
        $this->assertEquals($sequenceNumber, $event->getSequenceNumber());
        $this->assertNotEmpty($event->getOccurredAt());
    }

    public function testOfDeleted(): void {
        $id = new GroupChatId();
        $sequenceNumber = 4;
        $executorId = new UserAccountId();

        $event = GroupChatEventFactory::ofDeleted($id, $sequenceNumber, $executorId);
        $this->assertSame($id, $event->getAggregateId());
        $this->assertSame($executorId, $event->getExecutorId());
        $this->assertEquals($sequenceNumber, $event->getSequenceNumber());
        $this->assertNotEmpty($event->getOccurredAt());
    }
}
