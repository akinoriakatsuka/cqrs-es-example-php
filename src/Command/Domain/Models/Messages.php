<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

final readonly class Messages
{
    /**
     * @param array<Message> $messages
     */
    private function __construct(
        private array $messages
    ) {
    }

    public static function create(): self
    {
        return new self([]);
    }

    /**
     * @param array<Message> $messages
     */
    public static function fromMessages(array $messages): self
    {
        return new self($messages);
    }

    public function add(Message $message): self
    {
        return new self([...$this->messages, $message]);
    }

    public function edit(MessageId $message_id, string $new_text, UserAccountId $executor): self
    {
        $found = false;
        $new_messages = [];

        foreach ($this->messages as $message) {
            if ($message->getId()->equals($message_id)) {
                $found = true;

                // 送信者チェック
                if (!$message->getSenderId()->equals($executor)) {
                    throw new \DomainException(
                        'Only the sender can edit the message: ' . $executor->toString()
                    );
                }

                $new_messages[] = $message->withText($new_text);
            } else {
                $new_messages[] = $message;
            }
        }

        if (!$found) {
            throw new \DomainException('Message not found: ' . $message_id->toString());
        }

        return new self($new_messages);
    }

    public function remove(MessageId $message_id, UserAccountId $executor): self
    {
        $found = false;
        $new_messages = [];

        foreach ($this->messages as $message) {
            if ($message->getId()->equals($message_id)) {
                $found = true;

                // 送信者チェック
                if (!$message->getSenderId()->equals($executor)) {
                    throw new \DomainException(
                        'Only the sender can delete the message: ' . $executor->toString()
                    );
                }

                // メッセージを除外（削除）
                continue;
            }
            $new_messages[] = $message;
        }

        if (!$found) {
            throw new \DomainException('Message not found: ' . $message_id->toString());
        }

        return new self($new_messages);
    }

    public function findById(MessageId $message_id): ?Message
    {
        return array_find($this->messages, fn($message) => $message->getId()->equals($message_id));
    }

    public function toArray(): array
    {
        return [
            'values' => array_map(fn ($message) => $message->toArray(), $this->messages),
        ];
    }
}
