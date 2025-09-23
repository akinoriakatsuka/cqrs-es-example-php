<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;

class MessageIdTest extends TestCase {
    public function testGetValueGeneratesUlid(): void {
        $id = new MessageId();
        $value = $id->getValue();
        $this->assertNotEmpty($value);
        $this->assertEquals(26, strlen($value)); // ULID length
    }

    public function testGetValueWithSpecificValue(): void {
        $expectedValue = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id = new MessageId($expectedValue);
        $this->assertEquals($expectedValue, $id->getValue());
    }

    public function testEqualsWithSameValue(): void {
        $value = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id1 = new MessageId($value);
        $id2 = new MessageId($value);
        $this->assertTrue($id1->equals($id2));
    }

    public function testEqualsWithDifferentValues(): void {
        $id1 = new MessageId("01ARYZ6S41TSV4RRFFQ69G5FAV");
        $id2 = new MessageId("01ARYZ6S41TSV4RRFFQ69G5FAW");
        $this->assertFalse($id1->equals($id2));
    }

    public function testEqualsWithDifferentType(): void {
        $id = new MessageId();
        $this->assertFalse($id->equals($this->createMock(MessageId::class)));
    }
}