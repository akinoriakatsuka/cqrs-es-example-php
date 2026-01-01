<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

final readonly class Message
{
    public function __construct(
        private MessageId $id,
        private string $text,
        private UserAccountId $sender_id
    ) {
    }

    public function getId(): MessageId
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getSenderId(): UserAccountId
    {
        return $this->sender_id;
    }

    public function withText(string $new_text): self
    {
        return new self($this->id, $new_text, $this->sender_id);
    }

    public function equals(self $other): bool
    {
        return $this->id->equals($other->id);
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toArray(),
            'text' => $this->text,
            'sender_id' => $this->sender_id->toArray(),
        ];
    }


    /**
     * @deprecated Use MessageFactory::fromArray() instead
     */
    public static function fromArrayWithFactories(
        array $data,
        UserAccountIdFactory $userAccountIdFactory,
        MessageIdFactory $messageIdFactory
    ): self {
        return new self(
            $messageIdFactory->fromArray($data['id']),
            $data['text'],
            $userAccountIdFactory->fromArray($data['sender_id'])
        );
    }
}
