<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Ulid\Ulid;

readonly class UserAccountId {
    private string $value;

    public function __construct(?string $value  = null) {
        $this->value = $value ?? (string) Ulid::generate();
    }

    public function getValue(): string {
        return $this->value;
    }

    public function equals(UserAccountId $other): bool {
        return $this->value === $other->getValue();
    }
}
