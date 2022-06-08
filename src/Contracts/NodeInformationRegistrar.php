<?php

namespace Knevelina\Modernity\Contracts;

use Knevelina\Modernity\NodeInformation\NodeInformationMapping;

interface NodeInformationRegistrar
{
    public static function map(NodeInformationMapping $mapping): void;
}