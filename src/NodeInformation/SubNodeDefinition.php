<?php

namespace Knevelina\Modernity\NodeInformation;

use function in_array;

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
     * @var bool Whether 'null' is allowed as a sub node.
     */
    private readonly bool $isNullAllowed;

    /**
     * The valid class name(s) for this sub node. Use the 'NULL' constant to allow null.
     *
     * @var array<string>
     */
    private array $classNames;

    public function __construct(
        array|string $classNames,

        /** Whether the sub node is an array. */
        private bool $isArray = false
    ) {
        $this->classNames = is_array($classNames) ? $classNames : [$classNames];

        $this->isNullAllowed = in_array(self::NULL, $this->classNames);
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
    public function isNullAllowed(): bool
    {
        return $this->isNullAllowed;
    }
}