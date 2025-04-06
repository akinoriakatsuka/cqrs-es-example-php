<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use J5ik2o\EventStoreAdapterPhp\Aggregate;
use J5ik2o\EventStoreAdapterPhp\Event;
use J5ik2o\EventStoreAdapterPhp\EventStore;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use J5ik2o\EventStoreAdapterPhp\PersistenceException;
use J5ik2o\EventStoreAdapterPhp\SerializationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class GroupChatRepositoryTest extends TestCase {
    private EventStore $eventStore;
    private GroupChatRepositoryImpl $repository;
    private UserAccountId $adminId;
    private GroupChatName $name;
    private GroupChat $groupChat;
    private GroupChatEvent $event;

    protected function setUp(): void {
        $this->eventStore = EventStoreFactory::createInMemory();
        $this->repository = new GroupChatRepositoryImpl($this->eventStore);
        $this->adminId = new UserAccountId();
        $this->name = new GroupChatName("test-group-chat");
        $groupChatWithEventPair = GroupChat::create($this->name, $this->adminId);
        $this->groupChat = $groupChatWithEventPair->getGroupChat();
        $this->event = $groupChatWithEventPair->getEvent();
    }

    public function testStoreEventAndSnapshot(): void {
        // Act
        $this->repository->storeEventAndSnapshot($this->event, $this->groupChat);

        // Assert
        $retrievedGroupChat = $this->repository->findById($this->groupChat->getId());
        $this->assertNotNull($retrievedGroupChat);
        $this->assertEquals($this->groupChat->getId(), $retrievedGroupChat->getId());
        $this->assertEquals($this->name, $retrievedGroupChat->getName());
    }

    public function testStoreEvent(): void {
        // Arrange
        // First store the initial event and snapshot
        $this->repository->storeEventAndSnapshot($this->event, $this->groupChat);

        // Create a new event (add member)
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();
        $groupChatWithEventPair = $this->groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $this->adminId
        );
        $addMemberEvent = $groupChatWithEventPair->getEvent();
        $updatedGroupChat = $groupChatWithEventPair->getGroupChat();

        // Act
        $this->repository->storeEvent($addMemberEvent, $this->groupChat->getVersion());

        // Assert
        $retrievedGroupChat = $this->repository->findById($this->groupChat->getId());
        $this->assertNotNull($retrievedGroupChat);
        $this->assertEquals($updatedGroupChat->getSequenceNumber(), $retrievedGroupChat->getSequenceNumber());
        $this->assertNotNull($retrievedGroupChat->getMembers()->findByUserAccountId($userAccountId));
    }

    public function testFindById(): void {
        // Arrange
        $this->repository->storeEventAndSnapshot($this->event, $this->groupChat);

        // Act
        $retrievedGroupChat = $this->repository->findById($this->groupChat->getId());

        // Assert
        $this->assertNotNull($retrievedGroupChat);
        $this->assertEquals($this->groupChat->getId(), $retrievedGroupChat->getId());
        $this->assertEquals($this->name, $retrievedGroupChat->getName());
    }

    public function testFindByIdWithNonExistentId(): void {
        // Act
        $nonExistentId = new GroupChatId();
        $retrievedGroupChat = $this->repository->findById($nonExistentId);

        // Assert
        $this->assertNull($retrievedGroupChat);
    }

    public function testFindByIdWithEventsAfterSnapshot(): void {
        // Arrange
        // First store the initial event and snapshot
        $this->repository->storeEventAndSnapshot($this->event, $this->groupChat);

        // Create and store a new event (add member)
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();
        $groupChatWithEventPair = $this->groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $this->adminId
        );
        $addMemberEvent = $groupChatWithEventPair->getEvent();
        $updatedGroupChat = $groupChatWithEventPair->getGroupChat();
        $this->repository->storeEvent($addMemberEvent, $this->groupChat->getVersion());

        // Act
        $retrievedGroupChat = $this->repository->findById($this->groupChat->getId());

        // Assert
        $this->assertNotNull($retrievedGroupChat);
        $this->assertEquals($updatedGroupChat->getSequenceNumber(), $retrievedGroupChat->getSequenceNumber());
        $this->assertNotNull($retrievedGroupChat->getMembers()->findByUserAccountId($userAccountId));
    }

    public function testFindByIdWithPersistenceExceptionOnGetLatestSnapshot(): void {
        // Arrange
        /** @var EventStore|MockObject */
        $mockEventStore = $this->createMock(EventStore::class);
        $mockEventStore->method('getLatestSnapshotById')
            ->willThrowException(new PersistenceException('Test persistence exception'));

        $repository = new GroupChatRepositoryImpl($mockEventStore);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test persistence exception');

        $repository->findById(new GroupChatId());
    }

    public function testFindByIdWithSerializationExceptionOnGetLatestSnapshot(): void {
        // Arrange
        /** @var EventStore|MockObject */
        $mockEventStore = $this->createMock(EventStore::class);
        $mockEventStore->method('getLatestSnapshotById')
            ->willThrowException(new SerializationException('Test serialization exception'));

        $repository = new GroupChatRepositoryImpl($mockEventStore);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test serialization exception');

        $repository->findById(new GroupChatId());
    }

    public function testFindByIdWithUnexpectedAggregateType(): void {
        // Arrange
        /** @var EventStore|MockObject */
        $mockEventStore = $this->createMock(EventStore::class);

        // Create a mock Aggregate that is not a GroupChat
        $mockAggregate = $this->createMock(Aggregate::class);

        $mockEventStore->method('getLatestSnapshotById')
            ->willReturn($mockAggregate);

        $repository = new GroupChatRepositoryImpl($mockEventStore);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected Aggregate type');

        $repository->findById(new GroupChatId());
    }

    public function testFindByIdWithPersistenceExceptionOnGetEvents(): void {
        // Arrange
        /** @var EventStore|MockObject */
        $mockEventStore = $this->createMock(EventStore::class);

        // Return a valid GroupChat for getLatestSnapshotById
        $mockEventStore->method('getLatestSnapshotById')
            ->willReturn($this->groupChat);

        // Throw exception for getEventsByIdSinceSequenceNumber
        $mockEventStore->method('getEventsByIdSinceSequenceNumber')
            ->willThrowException(new PersistenceException('Test persistence exception on get events'));

        $repository = new GroupChatRepositoryImpl($mockEventStore);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test persistence exception on get events');

        $repository->findById(new GroupChatId());
    }

    public function testFindByIdWithSerializationExceptionOnGetEvents(): void {
        // Arrange
        /** @var EventStore|MockObject */
        $mockEventStore = $this->createMock(EventStore::class);

        // Return a valid GroupChat for getLatestSnapshotById
        $mockEventStore->method('getLatestSnapshotById')
            ->willReturn($this->groupChat);

        // Throw exception for getEventsByIdSinceSequenceNumber
        $mockEventStore->method('getEventsByIdSinceSequenceNumber')
            ->willThrowException(new SerializationException('Test serialization exception on get events'));

        $repository = new GroupChatRepositoryImpl($mockEventStore);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test serialization exception on get events');

        $repository->findById(new GroupChatId());
    }

    public function testFindByIdWithUnexpectedEventType(): void {
        // Arrange
        /** @var EventStore|MockObject */
        $mockEventStore = $this->createMock(EventStore::class);

        // Return a valid GroupChat for getLatestSnapshotById
        $mockEventStore->method('getLatestSnapshotById')
            ->willReturn($this->groupChat);

        // Create a mock Event that is not a GroupChatEvent
        $mockEvent = $this->createMock(Event::class);

        // Return an array with the mock event
        $mockEventStore->method('getEventsByIdSinceSequenceNumber')
            ->willReturn([$mockEvent]);

        $repository = new GroupChatRepositoryImpl($mockEventStore);

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unexpected event type');

        $repository->findById(new GroupChatId());
    }
}
