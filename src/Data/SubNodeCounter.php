<?php

namespace Knevelina\Modernity\Data;

use DomainException;
use InvalidArgumentException;
use Knevelina\Modernity\Enums\LanguageLevel;
use Knevelina\Modernity\NodeInformation\SubNode\SubNodeDefinition;
use Knevelina\Modernity\NodeInformation\Superclass\SuperclassInformation;
use Knevelina\Modernity\ServiceContainer;
use PhpParser\Node;
use RuntimeException;

use function get_class;
use function gettype;
use function is_array;
use function is_null;
use function is_object;

final class SubNodeCounter
{
    private int $hits = 0;

    /** @var array<string, LanguageLevelCounter> */
    private array $counters = [];

    /**
     * Mapping from AST sub node class name to amount of times we saw this class name.
     *
     * @var array<string, int>
     */
    private array $encounters;

    public function __construct(private readonly SubNodeDefinition $definition)
    {
        foreach ($this->definition->getClassNames() as $className) {
            $this->counters[$className] = new LanguageLevelCounter();
            $this->encounters[$className] = 0;
        }
    }

    public function getTuple(): LanguageLevelTuple
    {
        $tuple = new LanguageLevelTuple();

        foreach ($this->getClassNames() as $className) {
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

    /**
     * @return int
     */
    public function getHits(): int
    {
        return $this->hits;
    }

    /**
     * @return array<string>
     */
    public function getClassNames(): array
    {
        return $this->definition->getClassNames();
    }

    private static function getClass(mixed $subNode): string
    {
        if (is_scalar($subNode) || is_null($subNode)) {
            return match ($type = gettype($subNode)) {
                'NULL' => SubNodeDefinition::NULL,
                'integer' => SubNodeDefinition::INT,
                'double' => SubNodeDefinition::FLOAT,
                'string' => SubNodeDefinition::STRING,
                default => throw new RuntimeException(
                    sprintf('A scalar sub node of type "%s" was encountered, but this is not allowed.', $type)
                )
            };
        }

        if ($subNode instanceof Node) {
            /** @var SuperclassInformation $superClassInformation */
            $superClassInformation = ServiceContainer::nodeInformationMapping()
                ->get(get_class($subNode), SuperclassInformation::class);

            return $superClassInformation->getSuperClass();
        }

        throw new InvalidArgumentException('Invalid sub node type for given sub node definition' . gettype($subNode));
    }

    public function hit(mixed $subNode): int
    {
        if (is_array($subNode)) {
            if (!$this->definition->isArray()) {
                throw new RuntimeException(
                    'An array of sub nodes was encountered, but this is not allowed by the sub node definition.'
                );
            }

            foreach ($subNode as $value) {
                $this->hitSingle($value);
            }

            return count($subNode);
        }

        $this->hitSingle($subNode);
        return 1;
    }

    private function hitSingle(mixed $subNode): void
    {
        $this->hits++;

        $from = LanguageLevel::OLDEST;
//        $to = LanguageLevel::NEWEST;

        if ($subNode instanceof Node) {
            $from = $subNode->getAttribute('from') ?: LanguageLevel::OLDEST;
//            $to = $subNode->getAttribute('to') ?: LanguageLevel::NEWEST;
        }

        $class = self::getClass($subNode);

        if (!$this->definition->accepts($class)) {
            if (!is_object($subNode)) {
                throw new DomainException(
                    sprintf(
                        'Sub node definition does not accept a sub node with pseudo class %s - valid classes are [%s]',
                        $class,
                        implode(',', $this->definition->getClassNames())
                    )
                );
            } elseif ($this->definition->accepts($subNodeClass = get_class($subNode))) {
                $class = $subNodeClass;
            } else {
                throw new DomainException(
                    sprintf(
                        'Sub node definition does not accept a sub node with base class %s (subclass %s) - valid classes are [%s]',
                        $class,
                        get_class($subNode),
                        implode(',', $this->definition->getClassNames())
                    )
                );
            }
        }

        if ($from !== LanguageLevel::OLDEST) {
            $this->counters[$class]->hit($from);
            $this->encounters[$class]++;
        }
    }
}