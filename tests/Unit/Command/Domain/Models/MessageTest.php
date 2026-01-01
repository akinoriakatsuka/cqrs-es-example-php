<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;
    private RobinvdvleutenUlidValidator $validator;
    private MessageIdFactory $message_id_factory;
    private UserAccountIdFactory $user_account_id_factory;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->message_id_factory = new MessageIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
    }

    public function test_constructor_正常に生成できる(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();

        $message = new Message($message_id, 'Test Message', $sender_id);

        $this->assertEquals($message_id->toString(), $message->getId()->toString());
        $this->assertEquals('Test Message', $message->getText());
        $this->assertEquals($sender_id->toString(), $message->getSenderId()->toString());
    }

    public function test_withText_テキストを変更した新しいインスタンスを作成できる(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Original Text', $sender_id);

        $new_message = $message->withText('New Text');

        $this->assertEquals('New Text', $new_message->getText());
        $this->assertEquals($message_id->toString(), $new_message->getId()->toString());
        $this->assertEquals($sender_id->toString(), $new_message->getSenderId()->toString());
        $this->assertEquals('Original Text', $message->getText());
    }

    public function test_equals_同じIDのメッセージは等価(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id1 = $this->user_account_id_factory->create();
        $sender_id2 = $this->user_account_id_factory->create();

        $message1 = new Message($message_id, 'Text 1', $sender_id1);
        $message2 = new Message($message_id, 'Text 2', $sender_id2);

        $this->assertTrue($message1->equals($message2));
    }

    public function test_equals_異なるIDのメッセージは等価でない(): void
    {
        $message_id1 = $this->message_id_factory->create();
        $message_id2 = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();

        $message1 = new Message($message_id1, 'Same Text', $sender_id);
        $message2 = new Message($message_id2, 'Same Text', $sender_id);

        $this->assertFalse($message1->equals($message2));
    }

    public function test_toArray_配列に変換できる(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Test Message', $sender_id);

        $array = $message->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('text', $array);
        $this->assertArrayHasKey('sender_id', $array);
        $this->assertEquals('Test Message', $array['text']);
    }

    public function test_fromArray_配列から復元できる(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();

        $data = [
            'id' => ['value' => $message_id->toString()],
            'text' => 'Restored Message',
            'sender_id' => ['value' => $sender_id->toString()],
        ];

        $message = Message::fromArrayWithFactories(
            $data,
            $this->user_account_id_factory,
            $this->message_id_factory
        );

        $this->assertEquals($message_id->toString(), $message->getId()->toString());
        $this->assertEquals('Restored Message', $message->getText());
        $this->assertEquals($sender_id->toString(), $message->getSenderId()->toString());
    }

    public function test_toArray_fromArray_ラウンドトリップでデータが保持される(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $original_message = new Message($message_id, 'Round Trip Test', $sender_id);

        $array = $original_message->toArray();
        $restored_message = Message::fromArrayWithFactories(
            $array,
            $this->user_account_id_factory,
            $this->message_id_factory
        );

        $this->assertTrue($original_message->equals($restored_message));
        $this->assertEquals($original_message->getText(), $restored_message->getText());
        $this->assertEquals(
            $original_message->getSenderId()->toString(),
            $restored_message->getSenderId()->toString()
        );
    }
}
