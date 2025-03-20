<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests\Command\Domain\Models;

use PHPUnit\Framework\TestCase;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Messages;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\Message;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\MessageId;
use Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models\UserAccountId;

class MessagesTest extends TestCase {
    public function testConstructAndGetValues(): void {
        // Create a mock Message object since we don't want to test Message implementation here
        $messageId = $this->createMock(MessageId::class);
        $userAccountId = $this->createMock(UserAccountId::class);
        $message = $this->createMock(Message::class);

        $messages = new Messages([$message]);

        $values = $messages->getValues();
        $this->assertCount(1, $values);
        $this->assertSame($message, $values[0]);
    }
}
