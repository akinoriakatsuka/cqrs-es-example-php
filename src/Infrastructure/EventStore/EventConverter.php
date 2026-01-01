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
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\UlidValidator;

class EventConverter
{
    public function __construct(
        private UlidValidator $validator,
        private GroupChatIdFactory $groupChatIdFactory,
        private UserAccountIdFactory $userAccountIdFactory,
        private MemberIdFactory $memberIdFactory,
        private MessageIdFactory $messageIdFactory
    ) {
    }

    public function convert(array $data): GroupChatEvent
    {
        $type_name = $data['type_name'] ?? throw new \InvalidArgumentException('Missing type_name');

        return match ($type_name) {
            'GroupChatCreated' => GroupChatCreated::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory,
                $this->memberIdFactory,
                $this->validator
            ),
            'GroupChatRenamed' => GroupChatRenamed::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory
            ),
            'GroupChatDeleted' => GroupChatDeleted::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory
            ),
            'GroupChatMemberAdded' => GroupChatMemberAdded::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory,
                $this->memberIdFactory
            ),
            'GroupChatMemberRemoved' => GroupChatMemberRemoved::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory
            ),
            'GroupChatMessagePosted' => GroupChatMessagePosted::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory,
                $this->messageIdFactory
            ),
            'GroupChatMessageEdited' => GroupChatMessageEdited::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory,
                $this->messageIdFactory
            ),
            'GroupChatMessageDeleted' => GroupChatMessageDeleted::fromArrayWithFactories(
                $data,
                $this->groupChatIdFactory,
                $this->userAccountIdFactory,
                $this->messageIdFactory
            ),
            default => throw new \InvalidArgumentException("Unknown event type: {$type_name}"),
        };
    }
}
