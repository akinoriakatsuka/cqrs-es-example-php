<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\Ulid;

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

}
