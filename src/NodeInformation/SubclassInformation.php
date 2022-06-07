<?php

namespace Knevelina\Modernity;

/**
 * Information about the subclasses of (abstract) AST nodes.
 */
final class SubclassInformation implements NodeInformation
{
    public function __construct(private readonly array $subclasses)
    {
    }

    public function getSubclasses(): array
    {
        return $this->subclasses;
    }

    public function hasSubclasses(): bool
    {
        return count($this->subclasses) > 0;
    }
}