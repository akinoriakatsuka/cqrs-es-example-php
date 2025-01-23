<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Models;

use Ulid\Ulid;

readonly class MemberId {
    private string $value;

    public function __construct() {
        $value = Ulid::generate();
        $this->value = (string) $value;
    }

    public function getValue(): string {
        return $this->value;
    }
}
