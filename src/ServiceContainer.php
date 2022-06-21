<?php

namespace Knevelina\Modernity;

use Knevelina\Modernity\Data\LanguageLevelTupleStore;
use Knevelina\Modernity\NodeInformation\NodeInformationMapping;
use Knevelina\Modernity\NodeInformation\NodeInformationMappingFactory;

class ServiceContainer
{
    public static function nodeInformationMapping(): NodeInformationMapping
    {
        static $mapping;

        return $mapping ?: $mapping = NodeInformationMappingFactory::withDefaultRegistrars();
    }

    public static function languageLevelTupleStore(): LanguageLevelTupleStore
    {
        static $store;

        return $store ?: $store = new LanguageLevelTupleStore();
    }
}