<?php

namespace Knevelina\Modernity;

use Knevelina\Modernity\NodeInformation\NodeInformationMapping;
use Knevelina\Modernity\NodeInformation\NodeInformationMappingFactory;

class ServiceContainer
{
    public static function nodeInformationMapping(): NodeInformationMapping
    {
        static $mapping;

        return $mapping ?: NodeInformationMappingFactory::withDefaultRegistrars();
    }
}