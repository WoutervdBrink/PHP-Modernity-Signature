<?php

namespace Knevelina\Modernity\Visitors;

use Knevelina\Modernity\LanguageLevelInformation;
use Knevelina\Modernity\NodeInformationMapping;
use Knevelina\Modernity\NodeInformationMappingFactory;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class LanguageLevelVisitor extends NodeVisitorAbstract
{
    private NodeInformationMapping $mapping;

    public function __construct()
    {
        $this->mapping = NodeInformationMappingFactory::withDefaultRegistrars();
    }

    public function leaveNode(Node $node)
    {
        $class = get_class($node);

        /** @var LanguageLevelInformation $information */
        $information = $this->mapping->get($class, LanguageLevelInformation::class);

        $node->setAttribute('from', $information->getFrom($node));
        $node->setAttribute('to', $information->getTo($node));
    }
}