<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Processor;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
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
        $this->assertTrue($result instanceof GroupChatCreated);
        $this->assertSame($groupChatName, $result->getName());
    }
}
