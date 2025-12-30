<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;

interface EventStore
{
    /**
     * Save events with the specified version (without snapshot).
     * This corresponds to j5ik2o/event-store-adapter-php's persistEvent.
     * Used for updating existing aggregates when snapshot is not needed.
     * Panics/throws if event.isCreated() is true.
     *
     * @param GroupChatEvent[] $events
     * @param int $version The version to save
     */
    public function persistEvent(
        string $aggregate_id,
        int $version,
        array $events
    ): void;

    /**
     * Save events and snapshot.
     * This corresponds to j5ik2o/event-store-adapter-php's persistEventAndSnapshot.
     * Used for:
     * 1. Creating new aggregates (when event.isCreated() is true)
     * 2. Updating existing aggregates with snapshot (based on snapshot strategy)
     *
     * Internally checks event.isCreated() to decide between:
     * - createEventAndSnapshot (uses attribute_not_exists check)
     * - updateEventAndSnapshotOpt (uses version check)
     *
     * @param GroupChatEvent[] $events
     * @param int $version The version to save
     * @param GroupChat $aggregate The aggregate snapshot
     */
    public function persistEventAndSnapshot(
        string $aggregate_id,
        int $version,
        array $events,
        GroupChat $aggregate
    ): void;

    /**
     * @return GroupChatEvent[]
     */
    public function loadEvents(
        string $aggregate_id,
        int $since_seq_nr = 0
    ): array;

    /**
     * Get the latest snapshot by aggregate ID.
     *
     * @param string $aggregate_id
     * @return GroupChat|null Returns null if no snapshot exists
     */
    public function getLatestSnapshotById(string $aggregate_id): ?GroupChat;
}
