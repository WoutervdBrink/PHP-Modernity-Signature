<?php

namespace Knevelina\Modernity\NodeInformation;

use InvalidArgumentException;
use Knevelina\Modernity\Contracts\NodeInformation;

use function array_key_exists;
use function get_class;
use function is_subclass_of;

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

    /**
     * @param string $class
     * @param string $type
     * @return NodeInformation
     * @throws InvalidArgumentException
     */
    public function get(string $class, string $type): NodeInformation
    {
        if (array_key_exists($class, $this->nodeMap) && array_key_exists($type, $this->nodeMap[$class])) {
            return $this->nodeMap[$class][$type];
        }

        foreach ($this->nodeMap as $candidateClass => $informations) {
            if (is_subclass_of($class, $candidateClass) && array_key_exists($type, $informations)) {
                return $informations[$type];
            }
        }

        throw new InvalidArgumentException(
            sprintf('Information of type "%s" on node "%s" has not been registered!', $type, $class)
        );
    }

    /**
     * Get all AST node information of a certain type.
     *
     * @param string $type The class name of the node information.
     * @return array<string, NodeInformation>
     */
    public function getAllByType(string $type): array
    {
        $result = [];

        foreach ($this->nodeMap as $nodeClass => $informations) {
            foreach ($informations as $infoClass => $information) {
                if ($infoClass === $type || is_subclass_of($infoClass, $type)) {
                    $result[$nodeClass] = $information;
                }
            }
        }

        return $result;
    }
}