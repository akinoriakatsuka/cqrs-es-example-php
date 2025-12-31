<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use J5ik2o\EventStoreAdapterPhp\Aggregate;
use J5ik2o\EventStoreAdapterPhp\SnapshotSerializer as SnapshotSerializerInterface;

class SnapshotSerializer implements SnapshotSerializerInterface
{
    public function serialize(Aggregate $aggregate): string
    {
        // AggregateAdapterからjsonSerializeを使用
        $data = $aggregate->jsonSerialize();
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $data): array
    {
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Failed to deserialize snapshot');
        }

        return $decoded;
    }
}
