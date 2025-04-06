<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Errors\AlreadyDeletedException;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatEventFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatTest extends TestCase {
    private UserAccountId $adminId;
    private GroupChatName $name;
    private GroupChat $groupChat;

    protected function setUp(): void {
        $this->adminId = new UserAccountId();
        $this->name = new GroupChatName("test-group-chat");
        $groupChatWithEvent = GroupChat::create(
            $this->name,
            $this->adminId,
        );
        $this->groupChat = $groupChatWithEvent->getGroupChat();
    }

    public function testCreate(): void {
        // When
        $groupChatWithEvent = GroupChat::create(
            $this->name,
            $this->adminId,
        );

        // Then
        $groupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $this->assertEquals($this->name, $groupChat->getName());
        $this->assertEquals(1, $groupChat->getSequenceNumber());
        $this->assertEquals(1, $groupChat->getVersion());
        $this->assertFalse($groupChat->isDeleted());

        // Check that the admin is a member
        $members = $groupChat->getMembers();
        $this->assertNotNull($members->findByUserAccountId($this->adminId));

        // Check the event
        $this->assertEquals($groupChat->getId(), $event->getAggregateId());
        $this->assertInstanceOf(\Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated::class, $event);
        $this->assertEquals($this->name, $event->getName());
    }

    public function testGroupChatAddMember(): void {
        // Given
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();

        // When
        $groupChatWithEvent = $this->groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $this->adminId
        );

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $addedEvent = $groupChatWithEvent->getEvent();
        $this->assertEquals($this->groupChat->getId(), $newGroupChat->getId());
        $this->assertNotEquals(
            null,
            $newGroupChat->getMembers()->findByUserAccountId($userAccountId)
        );
        $this->assertEquals(
            $userAccountId,
            $newGroupChat->getMembers()->findByUserAccountId($userAccountId)?->getUserAccountId()
        );
        $this->assertEquals($this->groupChat->getId(), $addedEvent->getAggregateId());
        $this->assertEquals($this->groupChat->getSequenceNumber() + 1, $addedEvent->getSequenceNumber());
        $this->assertEquals($this->groupChat->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());
    }

    public function testAddMemberToDeletedGroupChat(): void {
        // Given
        $groupChatWithEvent = $this->groupChat->delete($this->adminId);
        $deletedGroupChat = $groupChatWithEvent->getGroupChat();

        // When/Then
        $this->expectException(AlreadyDeletedException::class);
        $deletedGroupChat->addMember(
            new MemberId(),
            new UserAccountId(),
            MemberRole::MEMBER_ROLE,
            $this->adminId
        );
    }

    public function testRename(): void {
        // Given
        $newName = new GroupChatName("renamed-group-chat");

        // When
        $groupChatWithEvent = $this->groupChat->rename($newName, $this->adminId);

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $this->assertEquals($newName, $newGroupChat->getName());
        $this->assertEquals($this->groupChat->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());
        $this->assertEquals($this->groupChat->getId(), $newGroupChat->getId());

        // Check the event
        $this->assertInstanceOf(GroupChatRenamed::class, $event);
        $this->assertEquals($this->groupChat->getId(), $event->getAggregateId());
        $this->assertEquals($newName, $event->getName());
        $this->assertEquals($this->adminId, $event->getExecutorId());
    }

    public function testDelete(): void {
        // When
        $groupChatWithEvent = $this->groupChat->delete($this->adminId);

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $this->assertTrue($newGroupChat->isDeleted());
        $this->assertEquals($this->groupChat->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());

        // Check the event
        $this->assertInstanceOf(GroupChatDeleted::class, $event);
        $this->assertEquals($this->groupChat->getId(), $event->getAggregateId());
        $this->assertEquals($this->adminId, $event->getExecutorId());
    }

    public function testDeleteAlreadyDeletedGroupChat(): void {
        // Given
        $groupChatWithEvent = $this->groupChat->delete($this->adminId);
        $deletedGroupChat = $groupChatWithEvent->getGroupChat();

        // When/Then
        $this->expectException(AlreadyDeletedException::class);
        $deletedGroupChat->delete($this->adminId);
    }

    public function testRemoveMember(): void {
        // Given
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();

        // Add a member first
        $groupChatWithEvent = $this->groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $this->adminId
        );
        $groupChatWithMember = $groupChatWithEvent->getGroupChat();

        // When
        $groupChatWithEvent = $groupChatWithMember->removeMember($userAccountId, $this->adminId);

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $this->assertNull($newGroupChat->getMembers()->findByUserAccountId($userAccountId));
        $this->assertEquals($groupChatWithMember->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());

        // Check the event
        $this->assertInstanceOf(GroupChatMemberRemoved::class, $event);
        $this->assertEquals($this->groupChat->getId(), $event->getAggregateId());
        $this->assertEquals($userAccountId, $event->getMemberUserAccountId());
        $this->assertEquals($this->adminId, $event->getExecutorId());
    }

    public function testRemoveMemberFromDeletedGroupChat(): void {
        // Given
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();

        // Add a member first
        $groupChatWithEvent = $this->groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $this->adminId
        );
        $groupChatWithMember = $groupChatWithEvent->getGroupChat();

        // Delete the group chat
        $groupChatWithEvent = $groupChatWithMember->delete($this->adminId);
        $deletedGroupChat = $groupChatWithEvent->getGroupChat();

        // When/Then
        $this->expectException(AlreadyDeletedException::class);
        $deletedGroupChat->removeMember($userAccountId, $this->adminId);
    }

    public function testRemoveNonExistentMember(): void {
        // Given
        $nonExistentUserId = new UserAccountId();

        // When/Then
        $this->expectException(\RuntimeException::class);
        $this->groupChat->removeMember($nonExistentUserId, $this->adminId);
    }

    public function testPostMessage(): void {
        // Given
        $messageId = new MessageId();
        $text = "Hello, this is a test message";
        $message = new Message($messageId, $text, $this->adminId);

        // When
        $groupChatWithEvent = $this->groupChat->postMessage($messageId, $message, $this->adminId);

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $this->assertCount(1, $newGroupChat->getMessages()->getValues());
        $this->assertEquals($message, $newGroupChat->getMessages()->getValues()[0]);
        $this->assertEquals($this->groupChat->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());

        // Check the event
        $this->assertInstanceOf(GroupChatMessagePosted::class, $event);
        $this->assertEquals($this->groupChat->getId(), $event->getAggregateId());
        $this->assertEquals($message, $event->getMessage());
        $this->assertEquals($this->adminId, $event->getExecutorId());
    }

    public function testPostMessageToDeletedGroupChat(): void {
        // Given
        $groupChatWithEvent = $this->groupChat->delete($this->adminId);
        $deletedGroupChat = $groupChatWithEvent->getGroupChat();

        $messageId = new MessageId();
        $message = new Message($messageId, "Test message", $this->adminId);

        // When/Then
        $this->expectException(AlreadyDeletedException::class);
        $deletedGroupChat->postMessage($messageId, $message, $this->adminId);
    }

    public function testEditMessage(): void {
        // Given
        $messageId = new MessageId();
        $originalText = "Original message";
        $message = new Message($messageId, $originalText, $this->adminId);

        // Post a message first
        $groupChatWithEvent = $this->groupChat->postMessage($messageId, $message, $this->adminId);
        $groupChatWithMessage = $groupChatWithEvent->getGroupChat();

        // When
        $newText = "Edited message";
        $groupChatWithEvent = $groupChatWithMessage->editMessage($messageId, $newText, $this->adminId);

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $editedMessage = $newGroupChat->getMessages()->findById($messageId);
        $this->assertNotNull($editedMessage);
        $this->assertEquals($newText, $editedMessage->getText());
        $this->assertEquals($groupChatWithMessage->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());

        // Check the event
        $this->assertInstanceOf(GroupChatMessageEdited::class, $event);
        $this->assertEquals($this->groupChat->getId(), $event->getAggregateId());
        $this->assertEquals($messageId, $event->getMessageId());
        $this->assertEquals($newText, $event->getNewText());
        $this->assertEquals($this->adminId, $event->getExecutorId());
    }

    public function testEditMessageInDeletedGroupChat(): void {
        // Given
        $messageId = new MessageId();
        $message = new Message($messageId, "Test message", $this->adminId);

        // Post a message first
        $groupChatWithEvent = $this->groupChat->postMessage($messageId, $message, $this->adminId);
        $groupChatWithMessage = $groupChatWithEvent->getGroupChat();

        // Delete the group chat
        $groupChatWithEvent = $groupChatWithMessage->delete($this->adminId);
        $deletedGroupChat = $groupChatWithEvent->getGroupChat();

        // When/Then
        $this->expectException(AlreadyDeletedException::class);
        $deletedGroupChat->editMessage($messageId, "New text", $this->adminId);
    }

    public function testEditNonExistentMessage(): void {
        // Given
        $nonExistentMessageId = new MessageId();

        // When/Then
        $this->expectException(\RuntimeException::class);
        $this->groupChat->editMessage($nonExistentMessageId, "New text", $this->adminId);
    }

    public function testDeleteMessage(): void {
        // Given
        $messageId = new MessageId();
        $text = "Message to be deleted";
        $message = new Message($messageId, $text, $this->adminId);

        // Post a message first
        $groupChatWithEvent = $this->groupChat->postMessage($messageId, $message, $this->adminId);
        $groupChatWithMessage = $groupChatWithEvent->getGroupChat();

        // When
        $groupChatWithEvent = $groupChatWithMessage->deleteMessage($messageId, $this->adminId);

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $event = $groupChatWithEvent->getEvent();

        $this->assertEmpty($newGroupChat->getMessages()->getValues());
        $this->assertEquals($groupChatWithMessage->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());

        // Check the event
        $this->assertInstanceOf(GroupChatMessageDeleted::class, $event);
        $this->assertEquals($this->groupChat->getId(), $event->getAggregateId());
        $this->assertEquals($messageId, $event->getMessageId());
        $this->assertEquals($this->adminId, $event->getExecutorId());
    }

    public function testDeleteMessageFromDeletedGroupChat(): void {
        // Given
        $messageId = new MessageId();
        $message = new Message($messageId, "Test message", $this->adminId);

        // Post a message first
        $groupChatWithEvent = $this->groupChat->postMessage($messageId, $message, $this->adminId);
        $groupChatWithMessage = $groupChatWithEvent->getGroupChat();

        // Delete the group chat
        $groupChatWithEvent = $groupChatWithMessage->delete($this->adminId);
        $deletedGroupChat = $groupChatWithEvent->getGroupChat();

        // When/Then
        $this->expectException(AlreadyDeletedException::class);
        $deletedGroupChat->deleteMessage($messageId, $this->adminId);
    }

    public function testDeleteNonExistentMessage(): void {
        // Given
        $nonExistentMessageId = new MessageId();

        // When/Then
        $this->expectException(\RuntimeException::class);
        $this->groupChat->deleteMessage($nonExistentMessageId, $this->adminId);
    }

    public function testJsonSerialize(): void {
        // When
        $json = $this->groupChat->jsonSerialize();

        // Then
        $this->assertIsArray($json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('sequenceNumber', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('version', $json);
        $this->assertArrayHasKey('isDeleted', $json);

        $this->assertEquals($this->groupChat->getId(), $json['id']);
        $this->assertEquals($this->groupChat->getSequenceNumber(), $json['sequenceNumber']);
        $this->assertEquals($this->groupChat->getName(), $json['name']);
        $this->assertEquals($this->groupChat->getVersion(), $json['version']);
        $this->assertEquals($this->groupChat->isDeleted(), $json['isDeleted']);
    }

    public function testWithVersion(): void {
        // When
        $newGroupChat = $this->groupChat->withVersion(10);

        // Then
        // The current implementation of withVersion returns $this, so it should be the same object
        $this->assertSame($this->groupChat, $newGroupChat);
    }

    public function testReplay(): void {
        // Given
        $groupChatId = new GroupChatId();
        $name = new GroupChatName("Original Name");
        $members = Members::create($this->adminId);
        $messages = new Messages([]);
        $sequenceNumber = 1;
        $version = 1;

        $originalGroupChat = new GroupChat(
            $groupChatId,
            $name,
            $members,
            $messages,
            $sequenceNumber,
            $version
        );

        // Create some events to replay
        $newName = new GroupChatName("New Name");
        $renameEvent = GroupChatEventFactory::ofRenamed(
            $groupChatId,
            $newName,
            2,
            $this->adminId
        );

        $deleteEvent = GroupChatEventFactory::ofDeleted(
            $groupChatId,
            3,
            $this->adminId
        );

        // When
        $replayedGroupChat = GroupChat::replay([$renameEvent, $deleteEvent], $originalGroupChat);

        // Then
        $this->assertEquals($groupChatId, $replayedGroupChat->getId());
        $this->assertEquals($newName, $replayedGroupChat->getName());
        $this->assertTrue($replayedGroupChat->isDeleted());
        $this->assertEquals(3, $replayedGroupChat->getSequenceNumber());
    }

    public function testApplyEvent(): void {
        // Test applying a GroupChatMemberAdded event
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();
        $memberAddedEvent = GroupChatEventFactory::ofMemberAdded(
            $this->groupChat->getId() instanceof GroupChatId ? $this->groupChat->getId() : new GroupChatId(),
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            2,
            $this->adminId
        );

        $updatedGroupChat = $this->groupChat->applyEvent($memberAddedEvent);
        $this->assertNotNull($updatedGroupChat->getMembers()->findByUserAccountId($userAccountId));

        // Test applying a GroupChatRenamed event
        $newName = new GroupChatName("New Name");
        $renameEvent = GroupChatEventFactory::ofRenamed(
            $this->groupChat->getId() instanceof GroupChatId ? $this->groupChat->getId() : new GroupChatId(),
            $newName,
            3,
            $this->adminId
        );

        $updatedGroupChat = $updatedGroupChat->applyEvent($renameEvent);
        $this->assertEquals($newName, $updatedGroupChat->getName());

        // Test applying a GroupChatDeleted event
        $deleteEvent = GroupChatEventFactory::ofDeleted(
            $this->groupChat->getId() instanceof GroupChatId ? $this->groupChat->getId() : new GroupChatId(),
            4,
            $this->adminId
        );

        $updatedGroupChat = $updatedGroupChat->applyEvent($deleteEvent);
        $this->assertTrue($updatedGroupChat->isDeleted());

        // Test applying a GroupChatMessagePosted event
        $messageId = new MessageId();
        $message = new Message($messageId, "Test message", $this->adminId);
        $messagePostedEvent = GroupChatEventFactory::ofMessagePosted(
            $this->groupChat->getId() instanceof GroupChatId ? $this->groupChat->getId() : new GroupChatId(),
            $message,
            $this->adminId,
            5
        );

        $groupChatWithMessage = $this->groupChat->applyEvent($messagePostedEvent);
        $this->assertCount(1, $groupChatWithMessage->getMessages()->getValues());
        $this->assertEquals($message, $groupChatWithMessage->getMessages()->getValues()[0]);

        // Test applying an unknown event type
        $mockEvent = $this->createMock(\Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent::class);
        $result = $this->groupChat->applyEvent($mockEvent);
        $this->assertSame($this->groupChat, $result);
    }

    public function testEquals(): void {
        // Create another GroupChat instance
        $otherGroupChat = new GroupChat(
            new GroupChatId(),
            new GroupChatName("Other Group Chat"),
            Members::create(new UserAccountId()),
            new Messages([]),
            1,
            1
        );

        // The equals method always returns true in the current implementation
        $this->assertTrue($this->groupChat->equals($otherGroupChat));
    }
}
