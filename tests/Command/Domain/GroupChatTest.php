<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
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

        // When

        // Then
        $this->assertEquals($groupChat->getMembers()->getValues()[0]->getUserAccountId(), $adminId);
    }
}
