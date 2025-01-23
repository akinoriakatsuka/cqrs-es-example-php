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
}
