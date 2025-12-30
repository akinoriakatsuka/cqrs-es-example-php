<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\EventStore;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatCreated;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatEvent;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageEdited;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatRenamed;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

class EventConverter
{
    public function __construct(
        private UlidValidator $validator
    ) {
    }

    public function convert(array $data): GroupChatEvent
    {
        $type_name = $data['type_name'] ?? throw new \InvalidArgumentException('Missing type_name');

        return match ($type_name) {
            'GroupChatCreated' => GroupChatCreated::fromArray($data, $this->validator),
            'GroupChatRenamed' => GroupChatRenamed::fromArray($data, $this->validator),
            'GroupChatDeleted' => GroupChatDeleted::fromArray($data, $this->validator),
            'GroupChatMemberAdded' => GroupChatMemberAdded::fromArray($data, $this->validator),
            'GroupChatMemberRemoved' => GroupChatMemberRemoved::fromArray($data, $this->validator),
            'GroupChatMessagePosted' => GroupChatMessagePosted::fromArray($data, $this->validator),
            'GroupChatMessageEdited' => GroupChatMessageEdited::fromArray($data, $this->validator),
            'GroupChatMessageDeleted' => GroupChatMessageDeleted::fromArray($data, $this->validator),
            default => throw new \InvalidArgumentException("Unknown event type: {$type_name}"),
        };
    }
}
