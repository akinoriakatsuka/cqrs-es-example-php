<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatName;
use PHPUnit\Framework\TestCase;

class GroupChatNameTest extends TestCase
{
    public function test_有効な名前で生成できる(): void
    {
        $name = new GroupChatName('グループチャット1');

        $this->assertInstanceOf(GroupChatName::class, $name);
        $this->assertEquals('グループチャット1', $name->toString());
    }

    public function test_64文字制限を超えるとエラー(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GroupChat name must be 64 characters or less');

        $long_name = str_repeat('あ', 65);
        new GroupChatName($long_name);
    }

    public function test_空文字でエラー(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GroupChat name cannot be empty');

        new GroupChatName('');
    }

    public function test_等価性判定(): void
    {
        $name1 = new GroupChatName('グループチャット1');
        $name2 = new GroupChatName('グループチャット1');
        $name3 = new GroupChatName('グループチャット2');

        $this->assertTrue($name1->equals($name2));
        $this->assertFalse($name1->equals($name3));
    }
}
