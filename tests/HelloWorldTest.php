<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Tests;

use Akinoriakatsuka\CqrsEsExamplePhp\HelloWorld;
use PHPUnit\Framework\TestCase;

class HelloWorldTest extends TestCase {
    /**
     * @return void
     */
    public function testSayHello() {
        $helloWorld = new HelloWorld();
        $this->assertEquals("Hello, World!", $helloWorld->sayHello());
    }
}
