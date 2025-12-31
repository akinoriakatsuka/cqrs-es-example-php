<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use J5ik2o\EventStoreAdapterPhp\Event;
use J5ik2o\EventStoreAdapterPhp\EventSerializer as EventSerializerInterface;

class EventSerializer implements EventSerializerInterface
{
    public function serialize(Event $event): string
    {
        // EventAdapterからjsonSerializeを使用
        $data = $event->jsonSerialize();
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function deserialize(string $data): array
    {
        $decoded = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new \RuntimeException('Failed to deserialize event');
        }

        return $decoded;
    }
}
