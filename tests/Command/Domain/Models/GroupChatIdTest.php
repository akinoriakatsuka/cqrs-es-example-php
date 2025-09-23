<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;

class GroupChatIdTest extends TestCase {
    public function testGetTypeName(): void {
        $id = new GroupChatId();
        $this->assertEquals("GroupChatId", $id->getTypeName());
    }

    public function testGetValueGeneratesUlid(): void {
        $id = new GroupChatId();
        $value = $id->getValue();
        $this->assertNotEmpty($value);
        $this->assertEquals(26, strlen($value)); // ULID length
    }

    public function testGetValueWithSpecificValue(): void {
        $expectedValue = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id = new GroupChatId($expectedValue);
        $this->assertEquals($expectedValue, $id->getValue());
    }

    public function testAsString(): void {
        $expectedValue = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id = new GroupChatId($expectedValue);
        $this->assertEquals($expectedValue, $id->asString());
    }

    public function testEqualsWithSameValue(): void {
        $value = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id1 = new GroupChatId($value);
        $id2 = new GroupChatId($value);
        $this->assertTrue($id1->equals($id2));
    }

    public function testEqualsWithDifferentValues(): void {
        $id1 = new GroupChatId("01ARYZ6S41TSV4RRFFQ69G5FAV");
        $id2 = new GroupChatId("01ARYZ6S41TSV4RRFFQ69G5FAW");
        $this->assertFalse($id1->equals($id2));
    }

    public function testEqualsWithDifferentType(): void {
        $id = new GroupChatId();
        $this->assertFalse($id->equals($this->createMock(\J5ik2o\EventStoreAdapterPhp\AggregateId::class)));
    }

    public function testJsonSerialize(): void {
        $expectedValue = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id = new GroupChatId($expectedValue);
        $json = $id->jsonSerialize();
        $this->assertArrayHasKey('value', $json);
        $this->assertEquals($expectedValue, $json['value']);
    }
}
