<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Ulid\Ulid;

class MemberId {
    private readonly string $value;

    public function __construct() {
        $value = Ulid::generate();
        $this->value = (string) $value;
    }

    public function getValue(): string {
        return $this->value;
    }
}
