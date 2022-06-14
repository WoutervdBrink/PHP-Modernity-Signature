<?php

namespace Knevelina\Modernity\Visitors;

use Knevelina\Modernity\Data\LanguageLevelTuple;
use Knevelina\Modernity\Data\NodeCounter;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function in_array;

final class ModernityVisitor extends NodeVisitorAbstract
{

    private int $hits = 0;

    /**
     * Class names that have been encountered by the visitor.
     *
     * @var array<string>
     */
    private array $classNames = [];

    /**
     * Mapping from AST node class name to sub node name to class alternative to language level counter.
     *
     * @var array<string, NodeCounter>
     */
    private array $counters = [];

    /**
     * Mapping from AST node class name to amount of times we saw this class name.
     *
     * @var array<string, int>
     */
    private array $encounters;

    public function beforeTraverse(array $nodes)
    {
        $this->hits = 0;

        $this->classNames = [];
        $this->counters = [];
        $this->encounters = [];
    }

    public function getTuple(): LanguageLevelTuple
    {
        $tuple = new LanguageLevelTuple();

        foreach ($this->classNames as $className) {
            $tuple = $tuple->add(
                $this->counters[$className]->getTuple()
                    ->normalize()
                    ->scale(
                        $this->hits > 0
                            ? $this->encounters[$className] / $this->hits
                            : 0
                    )
            );
        }

        return $tuple;
    }

    public function leaveNode(Node $node)
    {
        $className = get_class($node);

        $this->hits++;

        if (!in_array($className, $this->classNames)) {
            $this->classNames[] = $className;

            $this->counters[$className] = new NodeCounter($className);
            $this->encounters[$className] = 0;
        }

        $this->counters[$className]->hit($node);
        $this->encounters[$className]++;
    }
}