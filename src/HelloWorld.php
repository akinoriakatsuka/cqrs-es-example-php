<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp;

class HelloWorld {
    public function __construct() {
    }
    public function sayHello(): string {
        return "Hello, World!";
    }
}
