<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Factory\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;
use J5ik2o\EventStoreAdapterPhp\EventStore as J5EventStore;

class DynamoDBEventStore implements EventStore
{
    private GroupChatIdFactory $group_chat_id_factory;

    public function __construct(
        private J5EventStore $j5_event_store,
        private UlidValidator $validator
    ) {
        $this->group_chat_id_factory = new GroupChatIdFactory(
            new RobinvdvleutenUlidGenerator(),
            $validator
        );
    }

    public function persistEvent(
        string $aggregate_id,
        int $version,
        array $events
    ): void {
        // Check that events are not created (matches Go's panic behavior)
        foreach ($events as $event) {
            if ($event->isCreated()) {
                throw new \RuntimeException(
                    'persistEvent cannot be used for created events. Use persistEventAndSnapshot instead.'
                );
            }
        }

        // j5ik2o版は単一イベントを扱うので、ループで保存
        foreach ($events as $event) {
            $adapter = new GroupChatEventAdapter($event, $this->validator);
            $this->j5_event_store->persistEvent($adapter, $version);
        }
    }

    public function persistEventAndSnapshot(
        string $aggregate_id,
        int $version,
        array $events,
        \Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat $aggregate
    ): void {
        // j5ik2o版は単一イベントを扱うので、最初のイベントだけを使用
        // （通常、persistEventAndSnapshotは単一イベントで呼ばれる想定）
        if (empty($events)) {
            throw new \RuntimeException('No events provided');
        }

        $first_event = $events[0];
        $event_adapter = new GroupChatEventAdapter($first_event, $this->validator);
        $aggregate_adapter = new GroupChatAggregateAdapter($aggregate);

        // j5ik2o版のpersistEventAndSnapshotを呼び出す
        $this->j5_event_store->persistEventAndSnapshot($event_adapter, $aggregate_adapter);
    }

    public function loadEvents(
        string $aggregate_id,
        int $since_seq_nr = 0
    ): array {
        // AggregateIdを作成
        $group_chat_id = $this->group_chat_id_factory->fromString($aggregate_id);
        $aggregate_id_adapter = new GroupChatIdAdapter($group_chat_id);

        // イベントを取得
        $j5_events = $this->j5_event_store->getEventsByIdSinceSequenceNumber($aggregate_id_adapter, $since_seq_nr);

        // GroupChatEventに変換
        $events = [];
        foreach ($j5_events as $j5_event) {
            if ($j5_event instanceof GroupChatEventAdapter) {
                $events[] = $j5_event->getEvent();
            } else {
                // j5ik2o版から返されたイベントをデシリアライズして変換
                // これは通常発生しないはずですが、念のため
                throw new \RuntimeException('Unexpected event type returned from j5ik2o EventStore');
            }
        }

        return $events;
    }

    public function getLatestSnapshotById(string $aggregate_id): ?\Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat
    {
        // AggregateIdを作成
        $group_chat_id = $this->group_chat_id_factory->fromString($aggregate_id);
        $aggregate_id_adapter = new GroupChatIdAdapter($group_chat_id);

        // スナップショットを取得
        $snapshot = $this->j5_event_store->getLatestSnapshotById($aggregate_id_adapter);

        if ($snapshot === null) {
            return null;
        }

        if ($snapshot instanceof GroupChatAggregateAdapter) {
            return $snapshot->getGroupChat();
        }

        throw new \RuntimeException('Unexpected snapshot type returned from j5ik2o EventStore');
    }
}
