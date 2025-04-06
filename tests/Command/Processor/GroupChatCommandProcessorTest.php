<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Processor;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepository;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use PHPUnit\Framework\TestCase;

class GroupChatCommandProcessorTest extends TestCase {
    private GroupChatCommandProcessor $commandProcessor;
    private GroupChatRepository $repository;
    private UserAccountId $executorId;

    protected function setUp(): void {
        $eventStore = EventStoreFactory::createInMemory();
        $this->repository = new GroupChatRepositoryImpl($eventStore);
        $this->commandProcessor = new GroupChatCommandProcessor($this->repository);
        $this->executorId = new UserAccountId();
    }

    public function testCreateGroupChat(): void {
        $groupChatName = new GroupChatName("test");

        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);

        $this->assertTrue($result->isCreated());
        $this->assertInstanceOf(GroupChatCreated::class, $result);
        $this->assertSame($groupChatName, $result->getName());
    }

    public function testRenameGroupChat(): void {
        // First create a group chat
        $initialName = new GroupChatName("initial name");
        $createEvent = $this->commandProcessor->createGroupChat($initialName, $this->executorId);

        // Get the group chat ID from the created event
        $groupChatId = $createEvent->getAggregateId();

        // Rename the group chat
        $newName = new GroupChatName("new name");
        $renameEvent = $this->commandProcessor->renameGroupChat($groupChatId, $newName, $this->executorId);

        // Verify the rename event
        $this->assertFalse($renameEvent->isCreated());
        $this->assertInstanceOf(GroupChatRenamed::class, $renameEvent);
        $this->assertSame($newName, $renameEvent->getName());
        $this->assertSame($groupChatId, $renameEvent->getAggregateId());
        $this->assertSame($this->executorId, $renameEvent->getExecutorId());
    }

    public function testRenameNonExistentGroupChat(): void {
        // Try to rename a non-existent group chat
        $nonExistentId = new GroupChatId();
        $newName = new GroupChatName("new name");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("GroupChat not found with ID: " . json_encode($nonExistentId));

        $this->commandProcessor->renameGroupChat($nonExistentId, $newName, $this->executorId);
    }

    public function testDeleteGroupChat(): void {
        $groupChatName = new GroupChatName("test");

        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);
        $groupChatId = $result->getAggregateId();

        $result = $this->commandProcessor->deleteGroupChat($groupChatId, $this->executorId);

        $this->assertTrue($result instanceof GroupChatDeleted);
    }

    public function testDeleteNonExistentGroupChat(): void {
        // Try to delete a non-existent group chat
        $nonExistentId = new GroupChatId();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Group chat not found");

        $this->commandProcessor->deleteGroupChat($nonExistentId, $this->executorId);
    }

    public function testAddMember(): void {
        $groupChatName = new GroupChatName("test");

        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);

        $this->assertTrue($result->isCreated());
        $this->assertInstanceOf(GroupChatCreated::class, $result);
        $this->assertSame($groupChatName, $result->getName());

        $groupChatId = $result->getAggregateId();
        $memberUserAccountId = new UserAccountId();
        $memberRole = MemberRole::MEMBER_ROLE;

        $event = $this->commandProcessor->addMember(
            $groupChatId,
            $memberUserAccountId,
            $memberRole,
            $this->executorId
        );

        $actualGroupChatId = $event->getAggregateId();

        $this->assertSame($groupChatId, $actualGroupChatId);
        $this->assertInstanceOf(GroupChatMemberAdded::class, $event);
        $this->assertSame($memberUserAccountId, $event->getMember()->getUserAccountId());
    }

    public function testAddMemberToNonExistentGroupChat(): void {
        // Try to add a member to a non-existent group chat
        $nonExistentId = new GroupChatId();
        $memberUserAccountId = new UserAccountId();
        $memberRole = MemberRole::MEMBER_ROLE;

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Group chat not found");

        $this->commandProcessor->addMember(
            $nonExistentId,
            $memberUserAccountId,
            $memberRole,
            $this->executorId
        );
    }

    public function testRemoveMember(): void {
        // Create a group chat
        $groupChatName = new GroupChatName("test");
        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);
        $groupChatId = $result->getAggregateId();

        // Add a member
        $memberUserAccountId = new UserAccountId();
        $memberRole = MemberRole::MEMBER_ROLE;
        $this->commandProcessor->addMember(
            $groupChatId,
            $memberUserAccountId,
            $memberRole,
            $this->executorId
        );

        // Act
        $event = $this->commandProcessor->removeMember(
            $groupChatId,
            $memberUserAccountId,
            $this->executorId
        );

        // Assert
        $this->assertInstanceOf(GroupChatMemberRemoved::class, $event);
        $this->assertSame($groupChatId, $event->getAggregateId());
        $this->assertSame($memberUserAccountId, $event->getMemberUserAccountId());
        $this->assertSame($this->executorId, $event->getExecutorId());
    }

    public function testRemoveMemberFromNonExistentGroupChat(): void {
        // Try to remove a member from a non-existent group chat
        $nonExistentId = new GroupChatId();
        $memberUserAccountId = new UserAccountId();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Group chat not found");

        $this->commandProcessor->removeMember(
            $nonExistentId,
            $memberUserAccountId,
            $this->executorId
        );
    }

    public function testPostMessage(): void {
        // Create a group chat
        $groupChatName = new GroupChatName("test");
        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);
        $groupChatId = $result->getAggregateId();

        // Add a member
        $memberUserAccountId = new UserAccountId();
        $memberRole = MemberRole::MEMBER_ROLE;
        $this->commandProcessor->addMember(
            $groupChatId,
            $memberUserAccountId,
            $memberRole,
            $this->executorId
        );

        // Post a message
        $messageId = new MessageId();
        $message = new Message(
            $messageId,
            "Hello, world!",
            $memberUserAccountId
        );

        $event = $this->commandProcessor->postMessage(
            $groupChatId,
            $message,
            $memberUserAccountId
        );

        // Assert
        $this->assertInstanceOf(GroupChatMessagePosted::class, $event);
        $this->assertSame($message, $event->getMessage());
    }

    public function testPostMessageToNonExistentGroupChat(): void {
        // Try to post a message to a non-existent group chat
        $nonExistentId = new GroupChatId();
        $memberUserAccountId = new UserAccountId();
        $messageId = new MessageId();
        $message = new Message(
            $messageId,
            "Hello, world!",
            $memberUserAccountId
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Group chat not found");

        $this->commandProcessor->postMessage(
            $nonExistentId,
            $message,
            $memberUserAccountId
        );
    }

    public function testEditMessage(): void {
        // Create a group chat
        $groupChatName = new GroupChatName("test");
        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);
        $groupChatId = $result->getAggregateId();

        // Add a member
        $memberUserAccountId = new UserAccountId();
        $memberRole = MemberRole::MEMBER_ROLE;
        $this->commandProcessor->addMember(
            $groupChatId,
            $memberUserAccountId,
            $memberRole,
            $this->executorId
        );

        // Post a message
        $messageId = new MessageId();
        $originalText = "Hello, world!";
        $message = new Message(
            $messageId,
            $originalText,
            $memberUserAccountId
        );
        $this->commandProcessor->postMessage(
            $groupChatId,
            $message,
            $memberUserAccountId
        );

        // Edit the message
        $newText = "Updated message text";
        $event = $this->commandProcessor->editMessage(
            $groupChatId,
            $messageId,
            $newText,
            $memberUserAccountId
        );

        // Assert
        $this->assertInstanceOf(GroupChatMessageEdited::class, $event);
        $this->assertSame($groupChatId, $event->getAggregateId());
        $this->assertTrue($messageId->equals($event->getMessageId()));
        $this->assertSame($newText, $event->getNewText());
        $this->assertSame($memberUserAccountId, $event->getExecutorId());
    }

    public function testEditMessageInNonExistentGroupChat(): void {
        // Try to edit a message in a non-existent group chat
        $nonExistentId = new GroupChatId();
        $messageId = new MessageId();
        $newText = "Updated message text";

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Group chat not found");

        $this->commandProcessor->editMessage(
            $nonExistentId,
            $messageId,
            $newText,
            $this->executorId
        );
    }

    public function testDeleteMessage(): void {
        // Create a group chat
        $groupChatName = new GroupChatName("test");
        $result = $this->commandProcessor->createGroupChat($groupChatName, $this->executorId);
        $groupChatId = $result->getAggregateId();

        // Add a member
        $memberUserAccountId = new UserAccountId();
        $memberRole = MemberRole::MEMBER_ROLE;
        $this->commandProcessor->addMember(
            $groupChatId,
            $memberUserAccountId,
            $memberRole,
            $this->executorId
        );

        // Post a message
        $messageId = new MessageId();
        $message = new Message(
            $messageId,
            "Hello, world!",
            $memberUserAccountId
        );
        $this->commandProcessor->postMessage(
            $groupChatId,
            $message,
            $memberUserAccountId
        );

        // Delete the message
        $event = $this->commandProcessor->deleteMessage(
            $groupChatId,
            $messageId,
            $memberUserAccountId
        );

        // Assert
        $this->assertInstanceOf(GroupChatMessageDeleted::class, $event);
        $this->assertSame($groupChatId, $event->getAggregateId());
        $this->assertTrue($messageId->equals($event->getMessageId()));
        $this->assertSame($memberUserAccountId, $event->getExecutorId());
    }

    public function testDeleteMessageFromNonExistentGroupChat(): void {
        // Try to delete a message from a non-existent group chat
        $nonExistentId = new GroupChatId();
        $messageId = new MessageId();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Group chat not found");

        $this->commandProcessor->deleteMessage(
            $nonExistentId,
            $messageId,
            $this->executorId
        );
    }
}
