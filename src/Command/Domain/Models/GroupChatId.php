<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use J5ik2o\EventStoreAdapterPhp\AggregateId;
use Ulid\Ulid;

class GroupChatId implements AggregateId {
    public const TYPE_NAME = "GroupChatId";
    private readonly string $value;

    public function __construct() {
        $value = Ulid::generate();
        $this->value = (string) $value;
    }


    public function getTypeName(): string {
        return "";
    }

    public function getValue(): string {
        return "";
    }

    public function asString(): string {
        return "";
    }

    public function equals(AggregateId $other): bool {
        return true;
    }

    public function jsonSerialize(): mixed {
        return [
            "value" => $this->value,
        ];
    }
}
