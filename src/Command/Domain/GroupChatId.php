<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use J5ik2o\EventStoreAdapterPhp\AggregateId;

class GroupChatId implements AggregateId {
    private readonly string $typeName;
    private readonly string $value;

    public function __construct(string $value) {
        $this->typeName = "GroupChatId";
        $this->value = $value;
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
            "typeName" => $this->typeName,
            "value" => $this->value,
        ];
    }

}
