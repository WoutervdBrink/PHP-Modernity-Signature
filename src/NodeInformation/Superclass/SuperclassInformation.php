<?php

namespace Knevelina\Modernity\NodeInformation\Superclass;

use Knevelina\Modernity\Contracts\NodeInformation;

class SuperclassInformation implements NodeInformation
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