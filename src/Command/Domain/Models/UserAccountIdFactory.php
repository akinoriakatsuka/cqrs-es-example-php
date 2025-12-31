<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

final class UserAccountIdFactory
{
    public function __construct(
        private UlidGenerator $generator,
        private UlidValidator $validator
    ) {
    }

    public function create(): UserAccountId
    {
        return UserAccountId::generate($this->generator);
    }

    public function fromString(string $value): UserAccountId
    {
        return UserAccountId::fromString($value, $this->validator);
    }

    public function fromArray(array $data): UserAccountId
    {
        return UserAccountId::fromArray($data, $this->validator);
    }
}
