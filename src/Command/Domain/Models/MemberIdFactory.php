<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

final class MemberIdFactory
{
    public function __construct(
        private UlidGenerator $generator,
        private UlidValidator $validator
    ) {
    }

    public function create(): MemberId
    {
        return MemberId::generate($this->generator);
    }

    public function fromString(string $value): MemberId
    {
        return MemberId::fromString($value, $this->validator);
    }

    public function fromArray(array $data): MemberId
    {
        return MemberId::fromArray($data, $this->validator);
    }
}
