<?php

declare(strict_types=1);

namespace App\Infrastructure\EventStore;

use App\Command\Domain\Events\GroupChatEvent;
use App\Command\Domain\GroupChat;

class InMemoryEventStore implements EventStore
{
    /** @var array<string, GroupChatEvent[]> */
    private array $events = [];

    /** @var array<string, int> */
    private array $versions = [];

    public function persistEvent(
        string $aggregate_id,
        int $version,
        array $events
    ): void {
        // Check that event is not created (matches Go's panic behavior)
        foreach ($events as $event) {
            if ($event->isCreated()) {
                throw new \RuntimeException(
                    "persistEvent cannot be used for created events. Use persistEventAndSnapshot instead."
                );
            }
        }

        $current_version = $this->versions[$aggregate_id] ?? 0;

        // Version is the target version to save (e.g., 2 for second event)
        // Check that current version is one less (optimistic locking)
        if ($current_version !== $version - 1) {
            throw new \RuntimeException(
                "Version conflict: trying to save version {$version} but current version is {$current_version}"
            );
        }

        if (!isset($this->events[$aggregate_id])) {
            $this->events[$aggregate_id] = [];
        }

        foreach ($events as $event) {
            $this->events[$aggregate_id][] = $event;
        }

        $this->versions[$aggregate_id] = $version;
    }

    public function persistEventAndSnapshot(
        string $aggregate_id,
        int $version,
        array $events,
        GroupChat $aggregate
    ): void {
        // Check if this is a created event
        $is_created = false;
        foreach ($events as $event) {
            if ($event->isCreated()) {
                $is_created = true;
                break;
            }
        }

        if ($is_created) {
            // createEventAndSnapshot: Check that aggregate doesn't exist yet (like DynamoDB attribute_not_exists)
            if (isset($this->versions[$aggregate_id])) {
                throw new \RuntimeException(
                    "Aggregate {$aggregate_id} already exists"
                );
            }

            $this->events[$aggregate_id] = [];

            foreach ($events as $event) {
                $this->events[$aggregate_id][] = $event;
            }

            $this->versions[$aggregate_id] = $version;
        } else {
            // updateEventAndSnapshotOpt: Version check (optimistic locking)
            $current_version = $this->versions[$aggregate_id] ?? 0;

            if ($current_version !== $version - 1) {
                throw new \RuntimeException(
                    "Version conflict: trying to save version {$version} but current version is {$current_version}"
                );
            }

            if (!isset($this->events[$aggregate_id])) {
                $this->events[$aggregate_id] = [];
            }

            foreach ($events as $event) {
                $this->events[$aggregate_id][] = $event;
            }

            $this->versions[$aggregate_id] = $version;
        }
    }

    public function loadEvents(
        string $aggregate_id,
        int $since_seq_nr = 0
    ): array {
        if (!isset($this->events[$aggregate_id])) {
            return [];
        }

        $events = $this->events[$aggregate_id];

        if ($since_seq_nr > 0) {
            return array_slice($events, $since_seq_nr);
        }

        return $events;
    }
}
