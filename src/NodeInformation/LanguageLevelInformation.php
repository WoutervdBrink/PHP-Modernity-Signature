<?php

namespace Knevelina\Modernity;

use PhpParser\Node;

/**
 * Language level information about an AST node.
 */
final class LanguageLevelInformation implements NodeInformation
{
    public function __construct(
        /** The language level in which the node was first introduced. */
        private readonly LanguageLevelInspector $from,

        /** The last language level before the level in which the node was removed and/or deprecated, if any. */
        private readonly ?LanguageLevelInspector $to
    ) {
    }

    /**
     * Get the language level in which the node was first introduced.
     *
     * @param Node $node The node for which to retrieve the language level.
     * @return LanguageLevel
     */
    public function getFrom(Node $node): LanguageLevel
    {
        return $this->from->inspect($node) ?: LanguageLevel::PHP5_2;
    }

    /**
     * Get the last language level before the level in which the node was removed and/or deprecated, if any.
     *
     * @param Node $node The node for which to retrieve the language level.
     * @return LanguageLevel|null
     */
    public function getTo(Node $node): ?LanguageLevel
    {
        return $this->to?->inspect($node);
    }

    /**
     * Get whether the node is deprecated in the latest PHP version.
     *
     * @return bool
     */
    public function isDeprecated(): bool
    {
        return !is_null($this->to);
    }
}