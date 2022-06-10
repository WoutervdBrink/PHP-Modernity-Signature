<?php

namespace Knevelina\Modernity\Contracts;

use Knevelina\Modernity\NodeInformation\NodeInformationMapping;

/**
 * Registers information about AST node classes.
 */
interface NodeInformationRegistrar
{
    /**
     * Perform the mapping of AST node classes to information.
     *
     * @param NodeInformationMapping $mapping The node information mapping registry to perform the mapping on.
     * @return void
     */
    public static function map(NodeInformationMapping $mapping): void;
}