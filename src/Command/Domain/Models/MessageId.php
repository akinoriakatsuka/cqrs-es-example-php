<?php

declare(strict_types=1);

namespace App\Command\Domain\Models;

use App\Infrastructure\Ulid\Ulid;
use App\Infrastructure\Ulid\UlidGenerator;
use App\Infrastructure\Ulid\UlidValidator;

final readonly class MessageId
{
    private function __construct(
        private Ulid $id
    ) {
    }

    public static function fromString(string $value, UlidValidator $validator): self
    {
        return new self(Ulid::fromString($value, $validator));
    }

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

    public function __toString(): string
    {
        return $this->id->toString();
    }

    public function toArray(): array
    {
        return ['value' => $this->id->toString()];
    }

    public static function fromArray(array $data, UlidValidator $validator): self
    {
        return self::fromString($data['value'], $validator);
    }
}
