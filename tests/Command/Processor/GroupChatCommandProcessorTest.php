<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Processor;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Processor\GroupChatCommandProcessor;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;
use PHPUnit\Framework\TestCase;

class GroupChatCommandProcessorTest extends TestCase {
    public function testCreateGroupChat(): void {
        $eventStore = EventStoreFactory::createInMemory();
        $repository = new GroupChatRepositoryImpl($eventStore);
        $commandProcessor = new GroupChatCommandProcessor($repository);

        $groupChatName = new GroupChatName("test");
        $executorId = new UserAccountId();

        $result = $commandProcessor->createGroupChat($groupChatName, $executorId);

        $this->assertTrue($result->isCreated());
        $this->assertInstanceOf(GroupChatCreated::class, $result);
        $this->assertSame($groupChatName, $result->getName());
    }

    public function testRenameGroupChat(): void {
        $eventStore = EventStoreFactory::createInMemory();
        $repository = new GroupChatRepositoryImpl($eventStore);
        $commandProcessor = new GroupChatCommandProcessor($repository);

        // First create a group chat
        $initialName = new GroupChatName("initial name");
        $executorId = new UserAccountId();
        $createEvent = $commandProcessor->createGroupChat($initialName, $executorId);
        
        // Get the group chat ID from the created event
        $groupChatId = $createEvent->getAggregateId();
        
        // Rename the group chat
        $newName = new GroupChatName("new name");
        $renameEvent = $commandProcessor->renameGroupChat($groupChatId, $newName, $executorId);
        
        // Verify the rename event
        $this->assertFalse($renameEvent->isCreated());
        $this->assertInstanceOf(GroupChatRenamed::class, $renameEvent);
        $this->assertSame($newName, $renameEvent->getName());
        $this->assertSame($groupChatId, $renameEvent->getAggregateId());
        $this->assertSame($executorId, $renameEvent->getExecutorId());
    }
}
