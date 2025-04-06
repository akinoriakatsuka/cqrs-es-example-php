<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class MessagesTest extends TestCase {
    private MessageId $messageId1;
    private MessageId $messageId2;
    private UserAccountId $senderId;
    private Message $message1;
    private Message $message2;
    private Messages $messages;

    protected function setUp(): void {
        $this->messageId1 = new MessageId();
        $this->messageId2 = new MessageId();
        $this->senderId = new UserAccountId();
        $this->message1 = new Message($this->messageId1, 'Message 1', $this->senderId);
        $this->message2 = new Message($this->messageId2, 'Message 2', $this->senderId);
        $this->messages = new Messages([$this->message1, $this->message2]);
    }

    public function testConstructAndGetValues(): void {
        $values = $this->messages->getValues();
        $this->assertCount(2, $values);
        $this->assertSame($this->message1, $values[0]);
        $this->assertSame($this->message2, $values[1]);
    }

    public function testFindById(): void {
        $foundMessage = $this->messages->findById($this->messageId1);
        $this->assertSame($this->message1, $foundMessage);

        $nonExistentId = new MessageId();
        $notFoundMessage = $this->messages->findById($nonExistentId);
        $this->assertNull($notFoundMessage);
    }

    public function testEditMessage(): void {
        $newText = 'Updated message text';
        $updatedMessages = $this->messages->editMessage($this->messageId1, $newText);

        // Original messages should be unchanged
        $originalValues = $this->messages->getValues();
        $this->assertSame('Message 1', $originalValues[0]->getText());

        // New messages collection should have the updated message
        $updatedValues = $updatedMessages->getValues();
        $this->assertCount(2, $updatedValues);
        $this->assertSame($newText, $updatedValues[0]->getText());
        $this->assertSame('Message 2', $updatedValues[1]->getText());

        // Sender and ID should remain the same
        $this->assertTrue($updatedValues[0]->getId()->equals($this->messageId1));
        $this->assertTrue($updatedValues[0]->getSenderId()->equals($this->senderId));
    }

    public function testEditMessageWithNonExistentId(): void {
        $nonExistentId = new MessageId();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Message not found with ID: ' . $nonExistentId->getValue());

        $this->messages->editMessage($nonExistentId, 'New text');
    }

    public function testDeleteMessage(): void {
        $updatedMessages = $this->messages->deleteMessage($this->messageId1);

        // Original messages should be unchanged
        $originalValues = $this->messages->getValues();
        $this->assertCount(2, $originalValues);

        // New messages collection should have one less message
        $updatedValues = $updatedMessages->getValues();
        $this->assertCount(1, $updatedValues);
        $this->assertSame($this->message2, $updatedValues[0]);
    }

    public function testDeleteMessageWithNonExistentId(): void {
        $nonExistentId = new MessageId();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Message not found with ID: ' . $nonExistentId->getValue());

        $this->messages->deleteMessage($nonExistentId);
    }
}
