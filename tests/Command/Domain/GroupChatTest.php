<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChatId;

class GroupChatTest extends TestCase {
    public function testCreateGroupChat(): void {
        $id = new GroupChatId("aaa");
        $name = "aaa";
        $sequenceNumber = 0;
        $version = 0;
        $groupChat = GroupChat::create($id, $sequenceNumber, $name, $version);

        $this->assertEquals($groupChat[0]->getId(), $id);
        $this->assertEquals($groupChat[0]->getSequenceNumber(), $sequenceNumber);
        $this->assertEquals($groupChat[0]->getName(), $name);
        $this->assertEquals($groupChat[0]->getVersion(), $version);

        $this->assertTrue(strlen($groupChat[1]->getId()) === 17 + 26);
        $this->assertEquals($groupChat[1]->getAggregateId(), $id);
    }
}
