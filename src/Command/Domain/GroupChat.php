<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

class GroupChat
{

    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
