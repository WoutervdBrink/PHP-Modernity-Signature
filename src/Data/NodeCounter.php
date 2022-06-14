<?php

namespace Knevelina\Modernity\Data;

use Knevelina\Modernity\NodeInformation\SubNode\SubNodeInformation;
use Knevelina\Modernity\ServiceContainer;
use PhpParser\Node;

final class NodeCounter
{
    private int $subNodesEncountered = 0;

    /** @var array<string, SubNodeCounter> */
    private array $subNodeCounters = [];

    public function __construct(private readonly string $name)
    {
        /** @var SubNodeInformation $subNodeInformation */
        $subNodeInformation = ServiceContainer::nodeInformationMapping()->get($name, SubNodeInformation::class);

        foreach ($subNodeInformation->getSubNodeDefinitions() as $key => $definition) {
            $this->subNodeCounters[$key] = new SubNodeCounter($definition);
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getTuple(): LanguageLevelTuple
    {
        $tuple = new LanguageLevelTuple();

        foreach ($this->subNodeCounters as $key => $counter) {
            $tuple = $tuple->add(
                $counter->getTuple()
                    ->normalize()
                    ->scale(
                        $this->subNodesEncountered > 0
                            ? $counter->getHits() / $this->subNodesEncountered
                            : 0
                    )
            );
        }

        return $tuple;
    }

    /**
     * Register a hit for a certain instance of the node class this counter is keeping track of.
     *
     * @param Node $node
     * @return void
     */
    public function hit(Node $node): void
    {
        foreach ($this->subNodeCounters as $key => $counter) {
            $this->subNodesEncountered += $counter->hit($node?->$key);
        }
    }
}