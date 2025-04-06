<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class MessageTest extends TestCase {
    private Message $message;
    private MessageId $id;
    private string $text;
    private UserAccountId $senderId;

    protected function setUp(): void {
        $this->id = new MessageId();
        $this->text = 'Test message content';
        $this->senderId = new UserAccountId();
        $this->message = new Message($this->id, $this->text, $this->senderId);
    }

    public function testGetId(): void {
        $this->assertSame($this->id, $this->message->getId());
    }

    public function testGetText(): void {
        $this->assertSame($this->text, $this->message->getText());
    }

    public function testGetSenderId(): void {
        $this->assertSame($this->senderId, $this->message->getSenderId());
    }

    public function testEquals(): void {
        // Same values should be equal
        $sameMessage = new Message($this->id, $this->text, $this->senderId);
        $this->assertTrue($this->message->equals($sameMessage));

        // Different ID should not be equal
        $differentId = new Message(new MessageId(), $this->text, $this->senderId);
        $this->assertFalse($this->message->equals($differentId));

        // Different text should not be equal
        $differentText = new Message($this->id, 'Different text', $this->senderId);
        $this->assertFalse($this->message->equals($differentText));

        // Different sender should not be equal
        $differentSender = new Message($this->id, $this->text, new UserAccountId());
        $this->assertFalse($this->message->equals($differentSender));
    }
}
