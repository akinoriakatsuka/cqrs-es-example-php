<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;

class GroupChatNameTest extends TestCase {
    public function testGetValue(): void {
        $name = new GroupChatName('Test Group');
        $this->assertEquals('Test Group', $name->getValue());
    }

    public function testEquals(): void {
        $name1 = new GroupChatName('Test Group');
        $name2 = new GroupChatName('Test Group');
        $name3 = new GroupChatName('Different Group');

        $this->assertTrue($name1->equals($name2));
        $this->assertFalse($name1->equals($name3));
    }
}
