<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use J5ik2o\EventStoreAdapterPhp\AggregateId;
use J5ik2o\EventStoreAdapterPhp\EventStore;

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
     * @return GroupChat|null
     */
    public function findById(AggregateId $id): ?GroupChat {
        $latestSnapshot = $this->eventStore->getLatestSnapshotById($id);
        if ($latestSnapshot === null) {
            return null;
        }

        $events = $this
                ->eventStore
                ->getEventsByIdSinceSequenceNumber(
                    $id,
                    $latestSnapshot->getSequenceNumber()
                );
        return GroupChat::replay($events, $latestSnapshot);
    }
}
