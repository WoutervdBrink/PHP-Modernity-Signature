<?php

namespace Knevelina\Modernity\Contracts;

use Knevelina\Modernity\Enums\LanguageLevel;
use PhpParser\Node;

/**
 * Inspects the language level of a certain AST node based on features used in the node.
 */
interface LanguageLevelInspector
{
    /**
     * Inspect the AST node and return the language level.
     *
     * @param Node $node The node to be inspected.
     * @return LanguageLevel|null The language level used in the node.
     */
    public function inspect(Node $node): ?LanguageLevel;
}