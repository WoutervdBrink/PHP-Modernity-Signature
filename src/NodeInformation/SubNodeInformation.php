<?php

namespace Knevelina\Modernity;

use InvalidArgumentException;
use Knevelina\Modernity\Contracts\NodeInformation;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt;
use PhpParser\Node\VariadicPlaceholder;

use function array_key_exists;

/**
 * Sub node information about an AST node.
 */
final class SubNodeInformation implements NodeInformation
{
    /**
     * Mapping from the name of the sub node property to the type(s) of sub nodes.
     *
     * @var array<string, SubNodeDefinition>
     */
    private array $subNodeDefinitions;

    public function __construct(private readonly NodeInformationMapping $mapping)
    {
        $this->subNodeDefinitions = [];
    }

    /**
     * @return array<string, SubNodeDefinition>
     */
    public function getSubNodeDefinitions(): array
    {
        return $this->subNodeDefinitions;
    }

    public function include(string $className): self
    {
        /** @var SubNodeInformation $info */
        $info = $this->mapping->get($className, SubNodeInformation::class);

        foreach ($info->getSubNodeDefinitions() as $subNode => $definition) {
            $this->with($subNode, $definition->getClassNames(), $definition->isArray());
        }

        return $this;
    }

    public function with(string $subNode, array|string $classNames, bool $isArray = false): self
    {
        if (array_key_exists($subNode, $this->subNodeDefinitions)) {
            throw new InvalidArgumentException(
                sprintf('Sub node definition is already defined for sub node "%s"!', $subNode)
            );
        }

        $this->subNodeDefinitions[$subNode] = new SubNodeDefinition($classNames, $isArray);

        return $this;
    }

    private function withMultiple(array|string $classNames, bool $isArray, string ...$subNodes): self
    {
        foreach ($subNodes as $subNode) {
            $this->with($subNode, $classNames, $isArray);
        }

        return $this;
    }

    public function withExpr(string $subNode = 'expr', string ...$others): self
    {
        return $this->withMultiple(Expr::class, false, $subNode, ...$others);
    }

    public function withExprs(string $subNode = 'exprs', string ...$others): self
    {
        return $this->withMultiple(Expr::class, true, $subNode, ...$others);
    }

    public function withStmts(string $subNode = 'stmts', string ...$others): self
    {
        return $this->withMultiple(Stmt::class, false, $subNode, ...$others);
    }

    public function withParams(): self
    {
        return $this->with('params', Param::class, true);
    }

    public function withAttrGroups(): self
    {
        return $this->with('attrGroups', AttributeGroup::class, true);
    }

    public function withArgs(): self
    {
        return $this->with('args', [Arg::class, VariadicPlaceholder::class], true);
    }

    public function withType(string $subName = 'type'): self
    {
        return $this->with(
            $subName,
            [SubNodeDefinition::NULL, Identifier::class, Name::class, ComplexType::class]
        );
    }

    public function withReturn(): self
    {
        return $this->withType('returnType');
    }
}