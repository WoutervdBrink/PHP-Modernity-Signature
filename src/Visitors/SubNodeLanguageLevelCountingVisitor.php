<?php

namespace Knevelina\Modernity\Visitors;

use InvalidArgumentException;
use Knevelina\Modernity\Enums\LanguageLevel;
use Knevelina\Modernity\NodeInformation\NodeInformationMapping;
use Knevelina\Modernity\NodeInformation\SubNodeDefinition;
use Knevelina\Modernity\NodeInformation\SubNodeInformation;
use Knevelina\Modernity\NodeInformation\SuperclassInformation;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

use function is_array;
use function is_float;
use function is_int;
use function is_string;

class SubNodeLanguageLevelCountingVisitor extends NodeVisitorAbstract
{
    /**
     * Mapping from AST node class name to sub node name to class alternative to language level counter.
     *
     * @var array<string, array<string, array<string, LanguageLevelCounter>>>
     */
    private array $counters;

    public function __construct(private readonly NodeInformationMapping $mapping)
    {
    }

    /**
     * @return array<string, array<string, LanguageLevelCounter>>
     */
    public function getCounters(): array
    {
        return $this->counters;
    }

    public function beforeTraverse(array $nodes)
    {
        $this->counters = [];
    }

    public function leaveNode(Node $node)
    {
        $className = get_class($node);

        /** @var SubNodeInformation $subNodeInformation */
        $subNodeInformation = $this->mapping->get($className, SubNodeInformation::class);

        foreach ($subNodeInformation->getSubNodeDefinitions() as $subNodeName => $definition) {
            $this->hitSubNode($className, $subNodeName, $node->{$subNodeName}, $definition);
        }
    }

    private function getCounter(string $className, string $subNodeName, string $subNodeClass): LanguageLevelCounter
    {
        if (!\array_key_exists($className, $this->counters)) {
            $this->counters[$className] = [];
        }

        $counters = &$this->counters[$className];

        if (!\array_key_exists($subNodeName, $counters)) {
            $counters[$subNodeName] = [];
        }

        $counters = &$counters[$subNodeName];

        if (!\array_key_exists($subNodeClass, $counters)) {
            $counters[$subNodeClass] = new LanguageLevelCounter();
        }

        return $counters[$subNodeClass];
    }

    private function hitSubNode(
        string $parentClassName,
        string $subNodeName,
        mixed $subNode,
        SubNodeDefinition $definition
    ): void {
        if (is_array($subNode)) {
            if (!$definition->isArray()) {
                throw new InvalidArgumentException(
                    sprintf(
                        'An array of sub nodes was provided, but this is not allowed by the sub node definition for "%s"."%s"',
                        $parentClassName,
                        $subNodeName
                    )
                );
            }

            foreach ($subNode as $node) {
                $this->hitSubNode($parentClassName, $subNodeName, $node, $definition);
            }

            return;
        }

        $className = match (true) {
            is_null($subNode) => SubNodeDefinition::NULL,
            is_int($subNode) => SubNodeDefinition::INT,
            is_float($subNode) => SubNodeDefinition::FLOAT,
            is_string($subNode) => SubNodeDefinition::STRING,
            $subNode instanceof Node => (/** @var SuperclassInformation $superClassInformation */ $superClassInformation = $this->mapping->get(
                get_class($subNode),
                SuperclassInformation::class
            ))->getSuperclass(),
            default => throw new InvalidArgumentException('Invalid sub node type for given sub node definition'),
        };

        $counter = $this->getCounter($parentClassName, $subNodeName, $className);

        if ($subNode instanceof Node) {
            $counter->hitRange(
                $subNode->getAttribute('from') ?: LanguageLevel::OLDEST,
                $subNode->getAttribute('to') ?: LanguageLevel::NEWEST
            );
        } else {
            // Scalars have been around forever.
            $counter->hitRange(LanguageLevel::OLDEST, LanguageLevel::NEWEST);
        }
    }
}