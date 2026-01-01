<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatIdTest extends TestCase
{
    private UlidValidator $validator;
    private UlidGenerator $generator;
    private GroupChatIdFactory $factory;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->factory = new GroupChatIdFactory($this->generator, $this->validator);
    }

    public function test_fromString_ULID形式の文字列で生成できる(): void
    {
        $ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $id = $this->factory->fromString($ulid);

        $this->assertInstanceOf(GroupChatId::class, $id);
        $this->assertEquals($ulid, $id->toString());
    }

    public function test_fromString_等価性判定が正しく動作する(): void
    {
        $ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $id1 = $this->factory->fromString($ulid);
        $id2 = $this->factory->fromString($ulid);
        $id3 = $this->factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');

        $this->assertTrue($id1->equals($id2));
        $this->assertFalse($id1->equals($id3));
    }

    public function test_fromString_toStringで文字列に変換できる(): void
    {
        $ulid = '01H42K4ABWQ5V2XQEP3A48VE0Z';
        $id = $this->factory->fromString($ulid);

        $this->assertEquals($ulid, $id->toString());
        $this->assertEquals('GroupChat-' . $ulid, (string)$id);
        $this->assertEquals('GroupChat-' . $ulid, $id->asString());
    }

    public function test_fromString_無効なULID形式でエラーになる(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ULID format');

        $this->factory->fromString('invalid-ulid');
    }

    public function test_fromString_空文字でエラーになる(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ULID cannot be empty');

        $this->factory->fromString('');
    }

    public function test_generate_ジェネレーターで生成できる(): void
    {
        $id = $this->factory->create();

        $this->assertInstanceOf(GroupChatId::class, $id);
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
        $custom_factory = new GroupChatIdFactory($generator, $this->validator);

        $id = $custom_factory->create();

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
        $custom_factory = new GroupChatIdFactory($this->generator, $validator);

        $id = $custom_factory->fromString('custom-valid');

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
        $custom_factory = new GroupChatIdFactory($this->generator, $validator);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ULID format');

        $custom_factory->fromString('any-value');
    }
}
