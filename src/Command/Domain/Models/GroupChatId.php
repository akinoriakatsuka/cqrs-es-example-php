<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use J5ik2o\EventStoreAdapterPhp\AggregateId;
use Ulid\Ulid;

class GroupChatId implements AggregateId {
    public const TYPE_NAME = "GroupChatId";
    private readonly string $value;

    public function __construct(?string $value = null) {
        $this->value = $value ?? (string) Ulid::generate();
    }

    public function getTypeName(): string {
        return self::TYPE_NAME;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function asString(): string {
        return $this->value;
    }

    public function equals(AggregateId $other): bool {
        return $other instanceof self && $this->value === $other->value;
    }

    /**
     * @return array<string, string>
     */
    public function jsonSerialize(): array {
        return [
            "value" => $this->value,
        ];
    }
}
