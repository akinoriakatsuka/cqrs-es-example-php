<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\InterfaseAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository\GroupChatRepositoryImpl;
use PHPUnit\Framework\TestCase;
use J5ik2o\EventStoreAdapterPhp\EventStore;

class GroupChatRepositoryTest extends TestCase {
    public function testGroupChatRepositoryOnMemoryFindById(): void {
        // Given
        $eventStore = $this->createMock(EventStore::class);
        // getSnapshotByIdのモック
        $eventStore->method('getLatestSnapshotById')
            ->willReturnCallback(function ($id) {
                return new GroupChat(
                    $id,
                    new GroupChatName("test"),
                    new Members([]),
                    new Messages([]),
                    1,
                    1
                );
            });
        $repository = new GroupChatRepositoryImpl(
            $eventStore
        );
        $adminId = new UserAccountId();
        $name = new GroupChatName("test");
        $groupChatWithEvent = GroupChat::create(
            $name,
            $adminId,
        );
        $groupChat = $groupChatWithEvent->getGroupChat();

        $memberId = new MemberId();
        $userAccountId = new UserAccountId();

        // When
        $groupChatWithEvent = $groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $adminId
        );
        $groupChat2 = $repository->findById($groupChat->getId());

        // Then
        $this->assertEquals($groupChat->getId(), $groupChat2->getId());
    }
}
