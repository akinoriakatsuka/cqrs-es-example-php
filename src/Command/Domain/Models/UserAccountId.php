<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Ulid\Ulid;

readonly class UserAccountId {
    private string $value;

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
