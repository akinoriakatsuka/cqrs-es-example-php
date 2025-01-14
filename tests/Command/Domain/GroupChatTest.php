<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;

class GroupChatTest extends TestCase
{

    public function testCreateGroupChat(): void
    {

        $name = "aaa";
        $groupChat = new GroupChat($name);

        $this->assertEquals($groupChat->getName(), $name);
    }
}
