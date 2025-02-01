<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

readonly class GroupChatName {
    private string $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function equals(GroupChatName $other): bool {
        return $this->value === $other->getValue();
    }
}
