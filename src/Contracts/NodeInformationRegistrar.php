<?php

namespace Knevelina\Modernity\Contracts;

use Knevelina\Modernity\NodeInformationMapping;

interface NodeInformationRegistrar
{
    public static function map(NodeInformationMapping $mapping): void;
}