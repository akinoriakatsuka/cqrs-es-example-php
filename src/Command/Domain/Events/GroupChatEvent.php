<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events;

use J5ik2o\EventStoreAdapterPhp\Event;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;

interface GroupChatEvent extends Event {
    public function getAggregateId(): GroupChatId;
}
