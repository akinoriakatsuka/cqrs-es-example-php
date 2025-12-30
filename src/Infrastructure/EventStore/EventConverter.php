<?php

declare(strict_types=1);

namespace App\Infrastructure\EventStore;

use App\Command\Domain\Events\GroupChatCreated;
use App\Command\Domain\Events\GroupChatDeleted;
use App\Command\Domain\Events\GroupChatEvent;
use App\Command\Domain\Events\GroupChatMemberAdded;
use App\Command\Domain\Events\GroupChatMemberRemoved;
use App\Command\Domain\Events\GroupChatMessageDeleted;
use App\Command\Domain\Events\GroupChatMessageEdited;
use App\Command\Domain\Events\GroupChatMessagePosted;
use App\Command\Domain\Events\GroupChatRenamed;
use App\Infrastructure\Ulid\UlidValidator;

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
