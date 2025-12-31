<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Events;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Events\GroupChatMessageDeleted;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\GroupChatId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class GroupChatMessageDeletedTest extends TestCase
{
    private RobinvdvleutenUlidValidator $validator;
    private RobinvdvleutenUlidGenerator $generator;
    private MessageIdFactory $message_id_factory;

    protected function setUp(): void
    {
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->message_id_factory = new MessageIdFactory($this->generator, $this->validator);
    }

    public function test_正常に生成できる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $message_id = $this->message_id_factory->create();
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1B', $this->validator);

        $event = GroupChatMessageDeleted::create(
            $aggregate_id,
            $message_id,
            1,
            $executor_id
        );

        $this->assertInstanceOf(GroupChatMessageDeleted::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
        $this->assertEquals('GroupChatMessageDeleted', $event->getTypeName());
        $this->assertFalse($event->isCreated());
        $this->assertEquals(1, $event->getSeqNr());
        $this->assertEquals($message_id, $event->getMessageId());
    }

    public function test_toArrayでシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $message_id = $this->message_id_factory->create();
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1B', $this->validator);

        $event = GroupChatMessageDeleted::create(
            $aggregate_id,
            $message_id,
            1,
            $executor_id
        );

        $array = $event->toArray();

        $this->assertEquals('GroupChatMessageDeleted', $array['type_name']);
        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('aggregate_id', $array);
        $this->assertArrayHasKey('message_id', $array);
        $this->assertArrayHasKey('executor_id', $array);
        $this->assertEquals(1, $array['seq_nr']);
        $this->assertArrayHasKey('occurred_at', $array);
    }

    public function test_fromArrayでデシリアライズできる(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $message_id = MessageId::generate($this->generator);
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1B', $this->validator);

        $original_event = GroupChatMessageDeleted::create(
            $aggregate_id,
            $message_id,
            1,
            $executor_id
        );

        $data = $original_event->toArray();
        $event = GroupChatMessageDeleted::fromArray($data, $this->validator);

        $this->assertInstanceOf(GroupChatMessageDeleted::class, $event);
        $this->assertEquals($aggregate_id->toString(), $event->getAggregateId());
    }

    public function test_ラウンドトリップでデータが保持される(): void
    {
        $aggregate_id = GroupChatId::fromString('01H42K4ABWQ5V2XQEP3A48VE0Z', $this->validator);
        $message_id = MessageId::generate($this->generator);
        $executor_id = UserAccountId::fromString('01H42K4ABWQ5V2XQEP3A48VE1B', $this->validator);

        $original_event = GroupChatMessageDeleted::create(
            $aggregate_id,
            $message_id,
            1,
            $executor_id
        );

        $array = $original_event->toArray();
        $restored_event = GroupChatMessageDeleted::fromArray($array, $this->validator);

        $this->assertEquals($original_event->getAggregateId(), $restored_event->getAggregateId());
        $this->assertEquals($original_event->getSeqNr(), $restored_event->getSeqNr());
        $this->assertEquals($original_event->getTypeName(), $restored_event->getTypeName());
        $this->assertEquals($original_event->getId(), $restored_event->getId());
        $this->assertEquals($original_event->getOccurredAt(), $restored_event->getOccurredAt());
        $this->assertTrue($original_event->getMessageId()->equals($restored_event->getMessageId()));
    }
}
