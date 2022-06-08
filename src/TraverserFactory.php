<?php

namespace Knevelina\Modernity;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;

final class TraverserFactory
{
    public static function fromVisitors(NodeVisitor ...$visitors): NodeTraverser
    {
        $traverser = new NodeTraverser();

        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        return $traverser;
    }
}