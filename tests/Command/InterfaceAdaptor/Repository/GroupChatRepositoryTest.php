<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use PHPUnit\Framework\TestCase;
use J5ik2o\EventStoreAdapterPhp\EventStoreFactory;

class GroupChatRepositoryTest extends TestCase {
    public function testFindById(): void {
        // Arrange
        $eventStore = EventStoreFactory::createInMemory();
        $repository = new GroupChatRepositoryImpl($eventStore);
        $adminId = new UserAccountId();
        $name = new GroupChatName("test");
        $groupChatWithEventPair = GroupChat::create($name, $adminId);
        $groupChat = $groupChatWithEventPair->getGroupChat();
        $event = $groupChatWithEventPair->getEvent();
        $repository->storeEventAndSnapshot($event, $groupChat);
        try {
            $groupChat2 = $repository->findById($groupChat->getId());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
        $this->assertNotNull($groupChat2);

        $groupChatWithEventPair2 = $groupChat2->addMember(
            new MemberId(),
            new UserAccountId(),
            MemberRole::MEMBER_ROLE,
            new UserAccountId()
        );
        $addMemberEvent = $groupChatWithEventPair2->getEvent();
        $repository->storeEvent($addMemberEvent, $groupChat2->getVersion());

        // Act
        try {
            $groupChat3 = $repository->findById($groupChat->getId());
        } catch (\Exception $e) {
            $this->fail($e->getMessage());
        }
        $this->assertNotNull($groupChat3);

        // Assert
        $this->assertEquals($groupChat->getId(), $groupChat3->getId());
    }
}
