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
        $groupChat = new GroupChat($id, $sequenceNumber, $name, $version);

        $this->assertEquals($groupChat->getId(), $id);
        $this->assertEquals($groupChat->getSequenceNumber(), $sequenceNumber);
        $this->assertEquals($groupChat->getName(), $name);
        $this->assertEquals($groupChat->getVersion(), $version);
    }
}
