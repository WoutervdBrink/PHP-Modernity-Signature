<?php

namespace Knevelina\Modernity;

interface NodeInformationRegistrar
{
    public static function map(NodeInformationMapping $mapping): void;
}