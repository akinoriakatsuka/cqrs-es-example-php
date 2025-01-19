<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

use Ulid\Ulid;

class UserAccountId {
    private readonly string $value;

    public function __construct() {
        $value = (string) Ulid::generate();
        $this->value = $value;
    }

    public function getValue(): string {
        return $this->value;
    }

    public function equals(UserAccountId $other): bool {
        return $this->value === $other->getValue();
    }
}
