<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatTest extends TestCase {
    public function testGroupChatAddMember(): void {
        // Given
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

        // Then
        $newGroupChat = $groupChatWithEvent->getGroupChat();
        $addedEvent = $groupChatWithEvent->getEvent();
        $this->assertEquals($groupChat->getId(), $newGroupChat->getId());
        $this->assertNotEquals(
            null,
            $newGroupChat->getMembers()->findByUserAccountId($userAccountId)
        );
        $this->assertEquals(
            $userAccountId,
            $newGroupChat->getMembers()->findByUserAccountId($userAccountId)?->getUserAccountId()
        );
        $this->assertEquals($groupChat->getId(), $addedEvent->getAggregateId());
        $this->assertEquals($groupChat->getSequenceNumber() + 1, $addedEvent->getSequenceNumber());
        $this->assertEquals($groupChat->getSequenceNumber() + 1, $newGroupChat->getSequenceNumber());
    }
}
