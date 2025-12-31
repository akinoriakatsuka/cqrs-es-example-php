<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid;

use InvalidArgumentException;

final readonly class Ulid
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = strtoupper($value);
    }

    public static function fromString(string $value, UlidValidator $validator): self
    {
        if ($value === '') {
            throw new InvalidArgumentException('ULID cannot be empty');
        }

        if (!$validator->isValid($value)) {
            throw new InvalidArgumentException('Invalid ULID format');
        }

        return new self($value);
    }

    public static function generate(?UlidGenerator $generator = null): self
    {
        if ($generator === null) {
            $generator = new RobinvdvleutenUlidGenerator();
        }
        return new self($generator->generate());
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
