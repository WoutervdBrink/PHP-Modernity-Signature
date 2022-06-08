<?php

namespace Knevelina\Modernity\NodeInformation;

class NodeInformationMappingFactory
{
    public static function withDefaultRegistrars(): NodeInformationMapping
    {
        $mapping = new NodeInformationMapping();

        LanguageLevelInformationRegistrar::map($mapping);
        SubclassInformationRegistrar::map($mapping);
        SubNodeInformationRegistrar::map($mapping);

        return $mapping;
    }
}