<?php

namespace Knevelina\Modernity\NodeInformation\SubNode;

use function array_unique;
use function in_array;
use function is_array;

final class SubNodeDefinition
{
    /** @var string Allow 'null' as a sub node. */
    public const NULL = 'null';

    /** @var string Allow a scalar integer as a sub node. */
    public const INT = 'int';

    /** @var string Allow a scalar float as a sub node. */
    public const FLOAT = 'float';

    /** @var string Allow a scalar string as a sub node. */
    public const STRING = 'string';

    /**
     * The valid class name(s) for this sub node. Use the 'NULL' constant to allow null.
     *
     * @var array<string>
     */
    private array $classNames;

    public function __construct(
        array|string $classNames,

        /** @var bool Whether the sub node is an array. */
        private bool $isArray,

        /** @var bool Whether 'null' is allowed as a sub node. */
        private readonly bool $nullable
    ) {
        $this->classNames = is_array($classNames) ? $classNames : [$classNames];

        if ($this->nullable) {
            $this->classNames[] = SubNodeDefinition::NULL;
        }

        sort($this->classNames);

        $this->classNames = array_unique($this->classNames);
    }

    public function accepts(string $className): bool
    {
        return in_array($className, $this->classNames);
    }

    /**
     * Get the valid class name(s) for this sub node.
     *
     * @return array
     */
    public function getClassNames(): array
    {
        return $this->classNames;
    }

    /**
     * Get whether the sub node is an array.
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return $this->isArray;
    }

    /**
     * Get whether 'null' is allowed as a sub node.
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }
}