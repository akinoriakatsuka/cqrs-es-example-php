<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMemberAdded;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Member;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MemberIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Role;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatMemberAddedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;
    private GroupChatIdFactory $group_chat_id_factory;
    private MemberIdFactory $member_id_factory;
    private UserAccountIdFactory $user_account_id_factory;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->group_chat_id_factory = new GroupChatIdFactory($this->generator, $this->validator);
        $this->member_id_factory = new MemberIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $member_id = $this->member_id_factory->create();
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $member = new Member($member_id, $user_account_id, Role::MEMBER);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $event = GroupChatMemberAdded::create(
            $aggregate_id,
            $member,
            1,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatMemberAdded::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatMemberAdded', $event->getTypeName());
        $this->assertFalse($event->isCreated());
        $this->assertEquals(1, $event->getSeqNr());
        $this->assertEquals($member, $event->getMember());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $member_id = $this->member_id_factory->create();
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $member = new Member($member_id, $user_account_id, Role::MEMBER);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $event = GroupChatMemberAdded::create(
            $aggregate_id,
            $member,
            1,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatMemberAdded', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('member', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(1, $array['seq_nr']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $member_id = $this->member_id_factory->create();
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $member = new Member($member_id, $user_account_id, Role::MEMBER);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $original_event = GroupChatMemberAdded::create(
            $aggregate_id,
            $member,
            1,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = GroupChatMemberAdded::fromArray($data, $this->validator);

        $this->assertInstanceOf(GroupChatMemberAdded::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $member_id = $this->member_id_factory->create();
        $user_account_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $member = new Member($member_id, $user_account_id, Role::MEMBER);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $original_event = GroupChatMemberAdded::create(
            $aggregate_id,
            $member,
            1,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = GroupChatMemberAdded::fromArray($array, $this->validator);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
        $this->assertEquals($original_event->getId(), $restored_event->getId());
        $this->assertEquals($original_event->getOccurredAt(), $restored_event->getOccurredAt());
        $this->assertTrue($original_event->getMember()->equals($restored_event->getMember()));
    }
}
