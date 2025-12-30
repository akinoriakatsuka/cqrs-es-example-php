<?php

declare(strict_types=1);

namespace App\Command\Domain\Models;

enum Role: int
{
    case MEMBER = 0;
    case ADMINISTRATOR = 1;

    public static function fromInt(int $value): self
    {
        return match ($value) {
            0 => self::MEMBER,
            1 => self::ADMINISTRATOR,
            default => throw new \InvalidArgumentException('Invalid role value: ' . $value),
        };
    }

    public function isAdministrator(): bool
    {
        return $this === self::ADMINISTRATOR;
    }

    public function equals(self $other): bool
    {
        return $this === $other;
    }
}
