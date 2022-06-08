<?php

namespace Knevelina\Modernity;

use InvalidArgumentException;
use Knevelina\Modernity\Contracts\NodeInformation;

use function array_key_exists;
use function get_class;

final class NodeInformationMapping
{
    /**
     * @var array Mapping from class name to children information.
     */
    private array $nodeMap;

    public function __construct()
    {
        $this->nodeMap = [];
    }

    public function map(string $class, NodeInformation $information): void
    {
        if (!array_key_exists($class, $this->nodeMap)) {
            $this->nodeMap[$class] = [];
        }

        $type = get_class($information);

        if (array_key_exists($type, $this->nodeMap[$class])) {
            throw new InvalidArgumentException(
                sprintf('Information of type "%s" on node "%s" has already been registered!', $type, $class)
            );
        }

        $this->nodeMap[$class][$type] = $information;
    }

    public function get(string $class, string $type): NodeInformation
    {
        if (!array_key_exists($class, $this->nodeMap)) {
            throw new InvalidArgumentException(
                sprintf('Information on node "%s" has not been registered!', $class)
            );
        }

        if (!array_key_exists($type, $this->nodeMap[$class])) {
            throw new InvalidArgumentException(
                sprintf('Information of type "%s" on node "%s" has not been registered!', $type, $class)
            );
        }

        return $this->nodeMap[$class][$type];
    }
}