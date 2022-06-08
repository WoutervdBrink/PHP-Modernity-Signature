<?php

namespace Knevelina\Modernity\NodeInformation;

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

    /**
     * @var string Unique identifier of this sub node definition.
     */
    private readonly string $identifier;

    public function __construct(
        array|string $classNames,

        /** @var bool Whether the sub node is an array. */
        private bool $isArray,

        /** @var bool Whether 'null' is allowed as a sub node. */
        private readonly bool $nullable
    ) {
        $this->classNames = is_array($classNames) ? $classNames : [$classNames];

        sort($this->classNames);

        $this->identifier = sprintf(
            '{classNames=[%s],isArray=%s,nullable=%s}',
            implode(',',$this->classNames),
            $this->isArray ? 'yes' : 'no',
            $this->nullable ? 'yes' : 'no'
        );
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
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
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