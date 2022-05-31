<?php

namespace Knevelina\Modernity\Visitors;

use Knevelina\Modernity\NodeInformationRepository;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class LanguageLevelVisitor extends NodeVisitorAbstract
{
    private NodeInformationRepository $repository;

    public function __construct()
    {
        $this->repository = new NodeInformationRepository();
    }

    public function leaveNode(Node $node)
    {
        $class = get_class($node);

        $information = $this->repository->getNodeInformation($class);

        $node->setAttribute('from', $information->getFrom($node));
        $node->setAttribute('to', $information->getTo($node));
    }
}