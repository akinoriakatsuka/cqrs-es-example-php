<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function test_ADMINISTRATOR生成(): void
    {
        $role = Role::ADMINISTRATOR;

        $this->assertInstanceOf(Role::class, $role);
        $this->assertTrue($role->isAdministrator());
        $this->assertEquals(1, $role->value);
    }

    public function test_MEMBER生成(): void
    {
        $role = Role::MEMBER;

        $this->assertInstanceOf(Role::class, $role);
        $this->assertFalse($role->isAdministrator());
        $this->assertEquals(0, $role->value);
    }

    public function test_整数値から生成(): void
    {
        $admin = Role::fromInt(1);
        $member = Role::fromInt(0);

        $this->assertTrue($admin->isAdministrator());
        $this->assertFalse($member->isAdministrator());
    }

    public function test_無効な値でエラー(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role value');

        Role::fromInt(999);
    }

    public function test_等価性判定(): void
    {
        $admin1 = Role::ADMINISTRATOR;
        $admin2 = Role::ADMINISTRATOR;
        $member = Role::MEMBER;

        $this->assertTrue($admin1->equals($admin2));
        $this->assertFalse($admin1->equals($member));
    }
}
