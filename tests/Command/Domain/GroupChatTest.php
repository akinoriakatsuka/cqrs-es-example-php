<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Members;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class GroupChatTest extends TestCase
{
    public function testCreateGroupChat(): void
    {
        $id = new GroupChatId("aaa");
        $name = new GroupChatName("bbb");
        $members = new Members([]);
        $messages = new Messages([]);
        $sequenceNumber = 0;
        $version = 0;
        $groupChat = GroupChat::create(
            $id,
            $name,
            $members,
            $messages,
            $sequenceNumber,
            $version
        );

        $this->assertEquals($groupChat[0]->getId(), $id);
        $this->assertEquals($groupChat[0]->getSequenceNumber(), $sequenceNumber);
        $this->assertEquals($groupChat[0]->getName(), $name);
        $this->assertEquals($groupChat[0]->getVersion(), $version);

        $this->assertTrue(strlen($groupChat[1]->getId()) === 17 + 26);
        $this->assertEquals($groupChat[1]->getAggregateId(), $id);
    }
}
