<?php

declare(strict_types=1);

namespace Tests\Unit\Command\Domain\Models;

use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessagesFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountIdFactory;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidGenerator;
use Akinoriakatsuka\CqrsEsExamplePhp\Infrastructure\Ulid\RobinvdvleutenUlidValidator;
use PHPUnit\Framework\TestCase;

class MessagesTest extends TestCase
{
    private RobinvdvleutenUlidGenerator $generator;
    private RobinvdvleutenUlidValidator $validator;
    private MessageIdFactory $message_id_factory;
    private UserAccountIdFactory $user_account_id_factory;
    private MessagesFactory $messages_factory;

    protected function setUp(): void
    {
        $this->generator = new RobinvdvleutenUlidGenerator();
        $this->validator = new RobinvdvleutenUlidValidator();
        $this->message_id_factory = new MessageIdFactory($this->generator, $this->validator);
        $this->user_account_id_factory = new UserAccountIdFactory($this->generator, $this->validator);
        $message_factory = new MessageFactory($this->user_account_id_factory, $this->message_id_factory);
        $this->messages_factory = new MessagesFactory($message_factory);
    }

    public function test_create_空のMessagesを作成できる(): void
    {
        $messages = Messages::create();

        $this->assertInstanceOf(Messages::class, $messages);
    }

    public function test_add_メッセージを追加できる(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Hello', $sender_id);

        $new_messages = $messages->add($message);

        $found = $new_messages->findById($message_id);
        $this->assertNotNull($found);
        $this->assertEquals('Hello', $found->getText());
    }

    public function test_findById_存在しないメッセージはnullを返す(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();

        $found = $messages->findById($message_id);

        $this->assertNull($found);
    }

    public function test_edit_正常にメッセージを編集できる(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Original', $sender_id);

        $messages_with_message = $messages->add($message);
        $edited_messages = $messages_with_message->edit($message_id, 'Edited', $sender_id);

        $found = $edited_messages->findById($message_id);
        $this->assertNotNull($found);
        $this->assertEquals('Edited', $found->getText());
    }

    public function test_edit_送信者以外は編集できない(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $other_user_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Original', $sender_id);

        $messages_with_message = $messages->add($message);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only the sender can edit the message');

        $messages_with_message->edit($message_id, 'Edited', $other_user_id);
    }

    public function test_edit_存在しないメッセージはエラー(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Message not found');

        $messages->edit($message_id, 'Edited', $sender_id);
    }

    public function test_remove_正常にメッセージを削除できる(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'To be deleted', $sender_id);

        $messages_with_message = $messages->add($message);
        $removed_messages = $messages_with_message->remove($message_id, $sender_id);

        $found = $removed_messages->findById($message_id);
        $this->assertNull($found);
    }

    public function test_remove_送信者以外は削除できない(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $other_user_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Message', $sender_id);

        $messages_with_message = $messages->add($message);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only the sender can delete the message');

        $messages_with_message->remove($message_id, $other_user_id);
    }

    public function test_remove_存在しないメッセージはエラー(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Message not found');

        $messages->remove($message_id, $sender_id);
    }

    public function test_toArray_配列に変換できる(): void
    {
        $messages = Messages::create();
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();
        $message = new Message($message_id, 'Test Message', $sender_id);

        $messages_with_message = $messages->add($message);
        $array = $messages_with_message->toArray();

        $this->assertArrayHasKey('values', $array);
        $this->assertCount(1, $array['values']);
        $this->assertEquals('Test Message', $array['values'][0]['text']);
    }

    public function test_fromArray_配列から復元できる(): void
    {
        $message_id = $this->message_id_factory->create();
        $sender_id = $this->user_account_id_factory->create();

        $data = [
            'values' => [
                [
                    'id' => ['value' => $message_id->toString()],
                    'text' => 'Restored Message',
                    'sender_id' => ['value' => $sender_id->toString()],
                ],
            ],
        ];

        $messages = $this->messages_factory->fromArray($data);

        $found = $messages->findById($message_id);
        $this->assertNotNull($found);
        $this->assertEquals('Restored Message', $found->getText());
    }

    public function test_fromArray_空の配列からも復元できる(): void
    {
        $data = ['values' => []];

        $messages = $this->messages_factory->fromArray($data);

        $this->assertInstanceOf(Messages::class, $messages);
    }
}
