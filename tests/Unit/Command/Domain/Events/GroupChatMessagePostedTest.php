<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePosted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessagePostedFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatMessagePostedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;
    private GroupChatIdFactory $group_chat_id_factory;
    private MessageIdFactory $message_id_factory;
    private UserAccountIdFactory $user_account_id_factory;
    private GroupChatMessagePostedFactory $group_chat_message_posted_factory;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->group_chat_id_factory = new GroupChatIdFactory($this->generator, $this->validator);
        $this->message_id_factory = new MessageIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
        $message_factory = new MessageFactory($this->user_account_id_factory, $this->message_id_factory);
        $this->group_chat_message_posted_factory = new GroupChatMessagePostedFactory(
            $this->group_chat_id_factory,
            $this->user_account_id_factory,
            $message_factory
        );
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $message = new Message($message_id, 'Test message', $sender_id);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $event = GroupChatMessagePosted::create(
            $aggregate_id,
            $message,
            1,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatMessagePosted::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatMessagePosted', $event->getTypeName());
        $this->assertFalse($event->isCreated());
        $this->assertEquals(1, $event->getSeqNr());
        $this->assertEquals($message, $event->getMessage());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $message = new Message($message_id, 'Test message', $sender_id);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $event = GroupChatMessagePosted::create(
            $aggregate_id,
            $message,
            1,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatMessagePosted', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(1, $array['seq_nr']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $message = new Message($message_id, 'Test message', $sender_id);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $original_event = GroupChatMessagePosted::create(
            $aggregate_id,
            $message,
            1,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = $this->group_chat_message_posted_factory->fromArray($data);

        $this->assertInstanceOf(GroupChatMessagePosted::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = $this->group_chat_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE0Z');
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1A');
        $message = new Message($message_id, 'Test message', $sender_id);
        $executor_id = $this->user_account_id_factory->fromString('01H42K4ABWQ5V2XQEP3A48VE1B');

        $original_event = GroupChatMessagePosted::create(
            $aggregate_id,
            $message,
            1,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = $this->group_chat_message_posted_factory->fromArray($array);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
        $this->assertEquals($original_event->getId(), $restored_event->getId());
        $this->assertEquals($original_event->getOccurredAt(), $restored_event->getOccurredAt());
        $this->assertTrue($original_event->getMessage()->equals($restored_event->getMessage()));
    }
}
