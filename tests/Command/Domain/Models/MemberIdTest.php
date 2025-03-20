<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberId;

class MemberIdTest extends TestCase {
    public function testConstruct(): void {
        $id = new MemberId();
        $this->assertNotEmpty($id->getValue());
    }

    public function testGetValue(): void {
        $id = new MemberId();
        $value = $id->getValue();
        $this->assertNotEmpty($value);
    }
}
