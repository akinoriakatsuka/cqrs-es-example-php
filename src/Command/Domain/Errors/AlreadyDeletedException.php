<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain\Errors;

use Exception;

class AlreadyDeletedException extends Exception {
    public function __construct(string $message = 'Already deleted') {
        parent::__construct($message);
    }
}
