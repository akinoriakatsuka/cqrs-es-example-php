<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use J5ik2o\EventStoreAdapterPhp\Aggregate;
use J5ik2o\EventStoreAdapterPhp\AggregateId;

class GroupChat implements Aggregate {
    private readonly GroupChatId $id;
    private readonly int $sequenceNumber;
    private readonly string $name;
    private readonly int $version;

    public function __construct(GroupChatId $id, int $sequenceNumber, string $name, int $version) {
        $this->id = $id;
        $this->sequenceNumber = $sequenceNumber;
        $this->name = $name;
        $this->version = $version;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getId(): AggregateId {
        return $this->id;
    }

    public function getSequenceNumber(): int {
        return 0;
    }

    public function getVersion(): int {
        return 0;
    }

    /**
     * Sets the version.
     *
     * @param int $version
     * @return Aggregate
     */
    public function withVersion(int $version): Aggregate {
        return $this;
    }

    public function equals(Aggregate $other): bool {
        return true;
    }

    public function jsonSerialize(): mixed {
        return [
            "id" => $this->id,
            "sequenceNumber" => $this->sequenceNumber,
            "name" => $this->name,
            "version" => $this->version,
        ];
    }
}
