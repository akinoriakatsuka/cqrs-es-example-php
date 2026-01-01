<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatCreatedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatDeletedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMemberAddedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMemberRemovedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMessageDeletedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMessageEditedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatMessagePostedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\Factory\GroupChatRenamedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;

class EventConverter
{
    public function __construct(
        private GroupChatCreatedFactory $groupChatCreatedFactory,
        private GroupChatDeletedFactory $groupChatDeletedFactory,
        private GroupChatRenamedFactory $groupChatRenamedFactory,
        private GroupChatMemberAddedFactory $groupChatMemberAddedFactory,
        private GroupChatMemberRemovedFactory $groupChatMemberRemovedFactory,
        private GroupChatMessagePostedFactory $groupChatMessagePostedFactory,
        private GroupChatMessageEditedFactory $groupChatMessageEditedFactory,
        private GroupChatMessageDeletedFactory $groupChatMessageDeletedFactory
    ) {
    }

    public function convert(array $data): GroupChatEvent
    {
        $type_name = $data['type_name'] ?? throw new \InvalidArgumentException('Missing type_name');

        return match ($type_name) {
            'GroupChatCreated' => $this->groupChatCreatedFactory->fromArray($data),
            'GroupChatRenamed' => $this->groupChatRenamedFactory->fromArray($data),
            'GroupChatDeleted' => $this->groupChatDeletedFactory->fromArray($data),
            'GroupChatMemberAdded' => $this->groupChatMemberAddedFactory->fromArray($data),
            'GroupChatMemberRemoved' => $this->groupChatMemberRemovedFactory->fromArray($data),
            'GroupChatMessagePosted' => $this->groupChatMessagePostedFactory->fromArray($data),
            'GroupChatMessageEdited' => $this->groupChatMessageEditedFactory->fromArray($data),
            'GroupChatMessageDeleted' => $this->groupChatMessageDeletedFactory->fromArray($data),
            default => throw new \InvalidArgumentException("Unknown event type: {$type_name}"),
        };
    }
}
