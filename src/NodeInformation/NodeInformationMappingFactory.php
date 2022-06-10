<?php

namespace Knevelina\Modernity\NodeInformation;

use Knevelina\Modernity\NodeInformation\LanguageLevel\LanguageLevelInformationRegistrar;
use Knevelina\Modernity\NodeInformation\Subclass\SubclassInformationRegistrar;
use Knevelina\Modernity\NodeInformation\SubNode\SubNodeInformationRegistrar;
use Knevelina\Modernity\NodeInformation\Superclass\SuperclassInformationRegistrar;

class NodeInformationMappingFactory
{
    public static function withDefaultRegistrars(): NodeInformationMapping
    {
        $mapping = new NodeInformationMapping();

        LanguageLevelInformationRegistrar::map($mapping);
        SubclassInformationRegistrar::map($mapping);
        SuperclassInformationRegistrar::map($mapping);
        SubNodeInformationRegistrar::map($mapping);

        return $mapping;
    }
}