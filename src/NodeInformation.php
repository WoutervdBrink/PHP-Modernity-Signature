<?php

namespace Knevelina\Modernity;

use PhpParser\Node;

/**
 * Information about an AST node.
 */
class NodeInformation
{
    public function __construct(
        /**
         * The class name of the AST node.
         */
        private readonly string $class,

        /**
         * @var LanguageLevel The language level in which the node was first introduced.
         */
        private readonly LanguageLevel $from,

        /**
         * @var LanguageLevel|null The language level in which the node was removed and/or deprecated, if any.
         */
        private readonly ?LanguageLevel $to = null,
    )
    {
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
        return $this->from;
    }

    /**
     * Get the language level in which the node was removed and/or deprecated, if any.
     *
     * @param Node $node The node for which to retrieve the language level.
     * @return LanguageLevel|null
     */
    public function getTo(Node $node): ?LanguageLevel
    {
        return $this->to;
    }

    public function isDeprecated(): bool
    {
        return !is_null($this->to);
    }
}