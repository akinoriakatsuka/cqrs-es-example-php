<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

class Members
{
    /** @var array<Member> */
    private readonly array $values;

    /**
     * @param array<Member> $values
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @return array<Member>
     */
    public function getValues(): array
    {
        return $this->values;
    }
}