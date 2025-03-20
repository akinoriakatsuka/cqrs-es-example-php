<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;

class GroupChatIdTest extends TestCase {
    public function testGetTypeName(): void {
        $id = new GroupChatId();
        $this->assertEquals("", $id->getTypeName());
    }

    public function testGetValue(): void {
        $id = new GroupChatId();
        $this->assertEquals("", $id->getValue());
    }

    public function testAsString(): void {
        $id = new GroupChatId();
        $this->assertEquals("", $id->asString());
    }

    public function testEquals(): void {
        $id1 = new GroupChatId();
        $id2 = new GroupChatId();
        $this->assertTrue($id1->equals($id2));
    }

    public function testJsonSerialize(): void {
        $id = new GroupChatId();
        $json = $id->jsonSerialize();
        $this->assertIsArray($json);
        $this->assertArrayHasKey('value', $json);
    }
}
