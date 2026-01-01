<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

final readonly class MemberId
{
    private const TYPE_PREFIX = 'Member';

    private function __construct(
        private Ulid $id
    ) {
    }

    public static function from(Ulid $ulid): self
    {
        return new self($ulid);
    }

    /**
     * @deprecated Use MemberIdFactory::fromString() instead. This method will be removed in future versions.
     */
    public static function fromString(string $value, UlidValidator $validator): self
    {
        // プレフィックスが付いている場合は削除
        if (str_starts_with($value, self::TYPE_PREFIX . '-')) {
            $value = substr($value, strlen(self::TYPE_PREFIX) + 1);
        }
        return new self(Ulid::fromString($value, $validator));
    }

    /**
     * @deprecated Use MemberIdFactory::create() instead. This method will be removed in future versions.
     */
    public static function generate(UlidGenerator $generator): self
    {
        return new self(Ulid::generate($generator));
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    public function toString(): string
    {
        return $this->id->toString();
    }

    public function asString(): string
    {
        return self::TYPE_PREFIX . '-' . $this->id->toString();
    }

    public function getTypeName(): string
    {
        return self::TYPE_PREFIX;
    }

    public function __toString(): string
    {
        return $this->asString();
    }

    public function toArray(): array
    {
        return ['value' => $this->id->toString()];
    }

    /**
     * @deprecated Use MemberIdFactory::fromArray() instead. This method will be removed in future versions.
     */
    public static function fromArray(array $data, UlidValidator $validator): self
    {
        return self::fromString($data['value'], $validator);
    }
}
