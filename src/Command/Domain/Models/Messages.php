<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

readonly class Messages {
    /** @var array<Message> */
    private array $values;

    /**
     * @param array<Message> $values
     */
    public function __construct(array $values) {
        $this->values = $values;
    }

    /**
     * @return array<Message>
     */
    public function getValues(): array {
        return $this->values;
    }

    /**
     * Find a message by its ID
     *
     * @param MessageId $messageId
     * @return Message|null
     */
    public function findById(MessageId $messageId): ?Message {
        foreach ($this->values as $message) {
            if ($message->getId()->equals($messageId)) {
                return $message;
            }
        }
        return null;
    }

    /**
     * Edit a message
     *
     * @param MessageId $messageId
     * @param string $newText
     * @return Messages
     * @throws \RuntimeException If message not found
     */
    public function editMessage(MessageId $messageId, string $newText): Messages {
        $newValues = [];
        $found = false;

        foreach ($this->values as $message) {
            if ($message->getId()->equals($messageId)) {
                $newValues[] = new Message(
                    $message->getId(),
                    $newText,
                    $message->getSenderId()
                );
                $found = true;
            } else {
                $newValues[] = $message;
            }
        }

        if (!$found) {
            throw new \RuntimeException("Message not found with ID: " . $messageId->getValue());
        }

        return new Messages($newValues);
    }

    /**
     * Delete a message
     *
     * @param MessageId $messageId
     * @return Messages
     * @throws \RuntimeException If message not found
     */
    public function deleteMessage(MessageId $messageId): Messages {
        $newValues = [];
        $found = false;

        foreach ($this->values as $message) {
            if (!$message->getId()->equals($messageId)) {
                $newValues[] = $message;
            } else {
                $found = true;
            }
        }

        if (!$found) {
            throw new \RuntimeException("Message not found with ID: " . $messageId->getValue());
        }

        return new Messages($newValues);
    }
}
