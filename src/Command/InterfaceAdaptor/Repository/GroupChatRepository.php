<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\Repository;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\GroupChat;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;

interface GroupChatRepository {
    public function storeEvent(GroupChatEvent $event, int $version): void;
    public function storeEventAndSnapshot(GroupChatEvent $event, GroupChat $snapshot): void;
    public function findById(GroupChatId $id): ?GroupChat;
}
