<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

final class GroupChatIdFactory
{
    public function __construct(
        private UlidGenerator $generator,
        private UlidValidator $validator
    ) {
    }

    public function create(): GroupChatId
    {
        return GroupChatId::generate($this->generator);
    }

    public function fromString(string $value): GroupChatId
    {
        return GroupChatId::fromString($value, $this->validator);
    }

    public function fromArray(array $data): GroupChatId
    {
        return GroupChatId::fromArray($data, $this->validator);
    }
}
