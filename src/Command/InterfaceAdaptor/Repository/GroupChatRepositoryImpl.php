<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use J5ik2o\EventStoreAdapterPhp\AggregateId;
use J5ik2o\EventStoreAdapterPhp\EventStore;
use J5ik2o\EventStoreAdapterPhp\PersistenceException;
use J5ik2o\EventStoreAdapterPhp\SerializationException;
use RuntimeException;

readonly class GroupChatRepositoryImpl implements GroupChatRepository {
    private EventStore $eventStore;

    public function __construct(EventStore $eventStore) {
        $this->eventStore = $eventStore;
    }

    /**
     * @param GroupChatEvent $event
     * @param int            $version
     * @return void
     */
    public function storeEvent(GroupChatEvent $event, int $version): void {
        $this->eventStore->persistEvent($event, $version);
    }

    /**
     * @param GroupChatEvent $event
     * @param GroupChat      $snapshot
     * @return void
     */
    public function storeEventAndSnapshot(GroupChatEvent $event, GroupChat $snapshot): void {
        $this->eventStore->persistEventAndSnapshot($event, $snapshot);
    }

    /**
     * @param AggregateId $id
     * @throws RuntimeException
     * @return ?GroupChat
     */
    public function findById(AggregateId $id): ?GroupChat {
        try {
            $latestSnapshot = $this->eventStore->getLatestSnapshotById($id);
        } catch (PersistenceException|SerializationException $e) {
            throw new RuntimeException($e->getMessage());
        }
        if ($latestSnapshot === null) {
            return null;
        }

        if ($latestSnapshot instanceof GroupChat) {
            $latestGroupChatSnapshot = $latestSnapshot;
        } else {
            throw new RuntimeException('Unexpected Aggregate type');
        }

        try {
            $events = $this->eventStore
                ->getEventsByIdSinceSequenceNumber(
                    $id,
                    $latestSnapshot->getSequenceNumber()
                );
        } catch (PersistenceException|SerializationException $e) {
            throw new RuntimeException($e->getMessage());
        }

        // GroupChatEvent以外のイベントを除外
        $groupChatEvents = [];
        foreach ($events as $event) {
            if ($event instanceof GroupChatEvent) {
                $groupChatEvents[] = $event;
            } else {
                throw new RuntimeException('Unexpected event type');
            }
        }

        return GroupChat::replay($groupChatEvents, $latestGroupChatSnapshot);
    }
}
