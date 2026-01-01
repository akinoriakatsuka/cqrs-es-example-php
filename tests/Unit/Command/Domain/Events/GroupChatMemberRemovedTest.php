<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemoved;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberRemovedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatMemberRemovedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;
    private GroupChatIdFactory $group_chat_id_factory;
    private UserAccountIdFactory $user_account_id_factory;
    private GroupChatMemberRemovedFactory $group_chat_member_removed_factory;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->group_chat_id_factory = new GroupChatIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
        $this->group_chat_member_removed_factory = new GroupChatMemberRemovedFactory(
            $this->group_chat_id_factory,
            $this->user_account_id_factory
        );
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $event = GroupChatMemberRemoved::create(
            $aggregate_id,
            $user_account_id,
            1,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatMemberRemoved::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatMemberRemoved', $event->getTypeName());
        $this->assertFalse($event->isCreated());
        $this->assertEquals(1, $event->getSeqNr());
        $this->assertEquals($user_account_id, $event->getUserAccountId());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $event = GroupChatMemberRemoved::create(
            $aggregate_id,
            $user_account_id,
            1,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatMemberRemoved', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('user_account_id', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(1, $array['seq_nr']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $original_event = GroupChatMemberRemoved::create(
            $aggregate_id,
            $user_account_id,
            1,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = $this->group_chat_member_removed_factory->fromArray($data);

        $this->assertInstanceOf(GroupChatMemberRemoved::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $original_event = GroupChatMemberRemoved::create(
            $aggregate_id,
            $user_account_id,
            1,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = $this->group_chat_member_removed_factory->fromArray($array);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
        $this->assertEquals($original_event->getId(), $restored_event->getId());
        $this->assertEquals($original_event->getOccurredAt(), $restored_event->getOccurredAt());
        $this->assertTrue($original_event->getUserAccountId()->equals($restored_event->getUserAccountId()));
    }
}
