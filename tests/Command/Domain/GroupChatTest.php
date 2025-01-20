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
        [$groupChat, $createdEvent] = GroupChat::create(
            $name,
            $adminId,
        );
        $memberId = new MemberId();
        $userAccountId = new UserAccountId();

        // When
        [$newGroupChat, $addedEvent] = $groupChat->addMember(
            $memberId,
            $userAccountId,
            MemberRole::MEMBER_ROLE,
            $adminId
        );

        // Then
        $this->assertEquals($groupChat->getMembers()->getValues()[0]->getUserAccountId(), $adminId);
        $this->assertEquals($groupChat->getVersion() + 1, $newGroupChat->getVersion());
    }
}
