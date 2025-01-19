<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

class Message
{
    private readonly MessageId $id;
    private readonly string $text;
    private readonly UserAccountId $senderId;

    public function __construct(
        MessageId $id,
        string $text,
        UserAccountId $senderId
    ) {
        $this->id = $id;
        $this->text = $text;
        $this->senderId = $senderId;
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
        return $this->senderId;
    }

    public function equals(Message $other): bool
    {
        return $this->id->equals($other->getId())
            && $this->text === $other->getText()
            && $this->senderId->equals($other->getSenderId());
    }
}
