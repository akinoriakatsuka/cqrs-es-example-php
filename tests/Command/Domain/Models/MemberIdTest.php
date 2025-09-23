<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;

class MemberIdTest extends TestCase {
    public function testGetValueGeneratesUlid(): void {
        $id = new MemberId();
        $value = $id->getValue();
        $this->assertNotEmpty($value);
        $this->assertEquals(26, strlen($value)); // ULID length
    }

    public function testGetValueWithSpecificValue(): void {
        $expectedValue = "01ARYZ6S41TSV4RRFFQ69G5FAV";
        $id = new MemberId($expectedValue);
        $this->assertEquals($expectedValue, $id->getValue());
    }
}
