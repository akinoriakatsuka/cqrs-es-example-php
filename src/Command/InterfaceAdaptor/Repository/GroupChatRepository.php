<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore\EventStore;

/**
 * GroupChatRepository provides persistence for GroupChat aggregates.
 * This corresponds to Go's GroupChatRepository in pkg/command/interfaceAdaptor/repository/group_chat_repository.go
 */
class GroupChatRepository
{
    public function __construct(
        private EventStore $event_store
    ) {
    }

    /**
     * Store saves the event and snapshot to the event store.
     * This corresponds to Go's: func (g *GroupChatRepositoryImpl) Store(event events.GroupChatEvent, snapshot *domain.GroupChat) mo.Option[error]
     *
     * @param GroupChatEvent $event
     * @param GroupChat $group_chat
     * @return void
     */
    public function store(GroupChatEvent $event, GroupChat $group_chat): void
    {
        $version = $group_chat->getVersion();

        // Match Go's behavior:
        // if event.IsCreated() || g.snapshotDecider != nil && g.snapshotDecider(event, snapshot)
        //     -> StoreEventWithSnapshot (calls PersistEventAndSnapshot)
        // else
        //     -> StoreEvent (calls PersistEvent)
        //
        // Note: snapshotDecider is not implemented yet, so only check isCreated()
        if ($event->isCreated()) {
            $this->event_store->persistEventAndSnapshot(
                $group_chat->getId()->toString(),
                $version,
                [$event],
                $group_chat
            );
        } else {
            $this->event_store->persistEvent(
                $group_chat->getId()->toString(),
                $version,
                [$event]
            );
        }
    }

    /**
     * FindById retrieves a GroupChat aggregate by its ID.
     * This corresponds to Go's: func (g *GroupChatRepositoryImpl) FindById(id *models.GroupChatId) mo.Result[mo.Option[domain.GroupChat]]
     *
     * @param GroupChatId $id
     * @return GroupChat|null
     */
    public function findById(GroupChatId $id): ?GroupChat
    {
        // Get the latest snapshot
        $snapshot = $this->event_store->getLatestSnapshotById($id->toString());

        if ($snapshot === null) {
            return null;
        }

        // Get events since the snapshot
        $events = $this->event_store->loadEvents($id->toString(), $snapshot->getSeqNr() + 1);

        // Replay events on the snapshot
        return $this->replayGroupChat($events, $snapshot);
    }

    /**
     * Replay events on a GroupChat snapshot to restore the current state.
     * This corresponds to Go's: func ReplayGroupChat(events []esa.Event, snapshot GroupChat) GroupChat
     *
     * @param GroupChatEvent[] $events
     * @param GroupChat $snapshot
     * @return GroupChat
     */
    private function replayGroupChat(array $events, GroupChat $snapshot): GroupChat
    {
        $result = $snapshot;
        foreach ($events as $event) {
            $result = $result->applyEvent($event);
        }
        return $result;
    }
}
