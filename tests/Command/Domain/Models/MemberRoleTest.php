<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberRole;

class MemberRoleTest extends TestCase {
    public function testToString(): void {
        $this->assertEquals('member', MemberRole::MEMBER_ROLE->toString());
        $this->assertEquals('admin', MemberRole::ADMIN_ROLE->toString());
    }

    public function testFromString(): void {
        $this->assertSame(MemberRole::MEMBER_ROLE, MemberRole::fromString('member'));
        $this->assertSame(MemberRole::ADMIN_ROLE, MemberRole::fromString('admin'));
    }

    public function testFromStringWithInvalidRole(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown role: invalid');
        MemberRole::fromString('invalid');
    }
}
