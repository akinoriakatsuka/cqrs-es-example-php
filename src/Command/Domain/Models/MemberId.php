<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Ulid\Ulid;

readonly class MemberId {
    public string $value;

    public function __construct(?string $value = null) {
        $this->value = $value ?? (string) Ulid::generate();
    }

    public function getValue(): string {
        return $this->value;
    }
}
