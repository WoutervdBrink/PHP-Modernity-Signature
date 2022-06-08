<?php

namespace Knevelina\Modernity\NodeInformation;

class SuperclassInformation implements \Knevelina\Modernity\Contracts\NodeInformation
{
    public function __construct(private readonly string $superclass)
    {
    }

    /**
     * @return string
     */
    public function getSuperclass(): string
    {
        return $this->superclass;
    }
}