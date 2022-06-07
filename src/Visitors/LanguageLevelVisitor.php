<?php

namespace Knevelina\Modernity\Visitors;

use Knevelina\Modernity\LanguageLevelInformationRegistrar;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

final class LanguageLevelVisitor extends NodeVisitorAbstract
{
    private LanguageLevelInformationRegistrar $repository;

    public function __construct()
    {
        $this->repository = new LanguageLevelInformationRegistrar();
    }

    public function leaveNode(Node $node)
    {
        $class = get_class($node);

        $information = $this->repository->getNodeInformation($class);

        $node->setAttribute('from', $information->getFrom($node));
        $node->setAttribute('to', $information->getTo($node));
    }
}