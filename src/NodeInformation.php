<?php

namespace Knevelina\Modernity;

use PhpParser\Node;

/**
 * Information about an AST node.
 */
final class NodeInformation
{
    public function __construct(
        private readonly string $class,
        private readonly LanguageLevelInspector $from,
        private readonly LanguageLevelInspector $to
    ) {
    }

    /**
     * Get the class name of the AST node.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
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
        return $this->to->inspect($node);
    }

    public function isDeprecated(): bool
    {
        return !is_null($this->to);
    }
}