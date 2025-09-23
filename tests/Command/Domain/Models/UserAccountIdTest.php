<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class UserAccountIdTest extends TestCase {
    public function testConstructorWithValue(): void {
        $expectedValue = "user-123";
        $id = new UserAccountId($expectedValue);
        $this->assertEquals($expectedValue, $id->getValue());
    }

    public function testGetValue(): void {
        $expectedValue = "test-user-456";
        $id = new UserAccountId($expectedValue);
        $this->assertEquals($expectedValue, $id->getValue());
    }

    public function testEqualsWithSameValue(): void {
        $value = "user-789";
        $id1 = new UserAccountId($value);
        $id2 = new UserAccountId($value);
        $this->assertTrue($id1->equals($id2));
    }

    public function testEqualsWithDifferentValues(): void {
        $id1 = new UserAccountId("user-123");
        $id2 = new UserAccountId("user-456");
        $this->assertFalse($id1->equals($id2));
    }

    public function testEqualsWithDifferentType(): void {
        $id = new UserAccountId("user-123");
        $this->assertFalse($id->equals($this->createMock(UserAccountId::class)));
    }
}
