<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use App\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use App\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use App\Infrastructure\Ulid\Ulid;
use App\Infrastructure\Ulid\UlidGenerator;
use App\Infrastructure\Ulid\UlidValidator;
use PHPUnit\Framework\TestCase;

class UlidTest extends TestCase
{
    private UlidValidator $validator;
    private UlidGenerator $generator;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
    }

    public function test_fromString_ULID形式の文字列で生成できる(): void
    {
        $ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $id = Ulid::fromString($ulid, $this->validator);

        $this->assertInstanceOf(Ulid::class, $id);
        $this->assertEquals($ulid, $id->toString());
    }

    public function test_fromString_等価性判定が正しく動作する(): void
    {
        $ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $id1 = Ulid::fromString($ulid, $this->validator);
        $id2 = Ulid::fromString($ulid, $this->validator);
        $id3 = Ulid::fromString('01H42K4ABWQ5V2XQEP3A48VE1A', $this->validator);

        $this->assertTrue($id1->equals($id2));
        $this->assertFalse($id1->equals($id3));
    }

    public function test_fromString_toStringで文字列に変換できる(): void
    {
        $ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $id = Ulid::fromString($ulid, $this->validator);

        $this->assertEquals($ulid, $id->toString());
        $this->assertEquals($ulid, (string)$id);
    }

    public function test_fromString_無効なULID形式でエラーになる(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ULID format');

        Ulid::fromString('invalid-ulid', $this->validator);
    }

    public function test_fromString_空文字でエラーになる(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ULID cannot be empty');

        Ulid::fromString('', $this->validator);
    }

    public function test_generate_ジェネレーターで生成できる(): void
    {
        $id = Ulid::generate($this->generator);

        $this->assertInstanceOf(Ulid::class, $id);
        $this->assertNotEmpty($id->toString());
    }

    public function test_generate_カスタムジェネレーターで生成できる(): void
    {
        $custom_ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $generator = new class ($custom_ulid) implements UlidGenerator {
            public function __construct(private string $ulid)
            {
            }
            public function generate(): string
            {
                return $this->ulid;
            }
        };

        $id = Ulid::generate($generator);

        $this->assertEquals($custom_ulid, $id->toString());
    }

    public function test_fromString_カスタムバリデーターで検証できる(): void
    {
        $validator = new class () implements UlidValidator {
            public function isValid(string $value): bool
            {
                return $value === 'custom-valid';
            }
        };

        $id = Ulid::fromString('custom-valid', $validator);

        $this->assertEquals('CUSTOM-VALID', $id->toString());
    }

    public function test_fromString_カスタムバリデーターで無効判定される(): void
    {
        $validator = new class () implements UlidValidator {
            public function isValid(string $value): bool
            {
                return false;
            }
        };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ULID format');

        Ulid::fromString('any-value', $validator);
    }
}
