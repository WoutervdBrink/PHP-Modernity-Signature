<?php

namespace Knevelina\Modernity\NodeInformation;

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
     * Get the mapping from the name of the sub node property to the type(s) of sub nodes.
     *
     * @return array<string, SubNodeDefinition>
     */
    public function getSubNodeDefinitions(): array
    {
        return $this->subNodeDefinitions;
    }

    /**
     * Include the sub node definition(s) from a previously mapped AST node.
     *
     * @param string $className The class name of the AST node to include the mappings from.
     * @return $this
     */
    public function include(string $className): self
    {
        /** @var SubNodeInformation $info */
        $info = $this->mapping->get($className, SubNodeInformation::class);

        foreach ($info->getSubNodeDefinitions() as $subNode => $definition) {
            $this->with($subNode, $definition->getClassNames(), $definition->isArray());
        }

        return $this;
    }

    /**
     * Add a sub node definition.
     *
     * @param string $subNode The name of the sub node.
     * @param array|string $classNames The allowed class name(s) of the sub node.
     * @param bool $isArray Whether the sub node is an array.
     * @return $this
     */
    public function with(string $subNode, array|string $classNames, bool $isArray = false, bool $nullable = false): self
    {
        if (array_key_exists($subNode, $this->subNodeDefinitions)) {
            throw new InvalidArgumentException(
                sprintf('Sub node definition is already defined for sub node "%s"!', $subNode)
            );
        }

        $this->subNodeDefinitions[$subNode] = new SubNodeDefinition($classNames, $isArray, $nullable);

        return $this;
    }

    /**
     * Add multiple sub nodes with identical definitions.
     *
     * @param array|string $classNames The allowed class name(s) of the sub nodes.
     * @param bool $isArray Whether the sub nodes are arrays.
     * @param string ...$subNodes The names of the sub nodes.
     * @return $this
     */
    private function withMultiple(array|string $classNames, bool $isArray, string ...$subNodes): self
    {
        foreach ($subNodes as $subNode) {
            $this->with($subNode, $classNames, $isArray);
        }

        return $this;
    }

    /**
     * Add a sub node which is an array of function call arguments. The name of the sub node is 'args'.
     *
     * @return $this
     */
    public function withArgs(): self
    {
        return $this->with('args', [Arg::class, VariadicPlaceholder::class], true);
    }

    /**
     * Add a sub node which is an array of attribute groups. The name of the sub node is 'attrGroups'.
     *
     * @return $this
     */
    public function withAttrGroups(): self
    {
        return $this->with('attrGroups', AttributeGroup::class, true);
    }

    /**
     * Add a sub node which is an expression.
     *
     * @param string $subNode The name of the expression sub node.
     * @param string ...$others The names of further expression sub nodes.
     * @return $this
     */
    public function withExpr(string $subNode = 'expr', string ...$others): self
    {
        return $this->withMultiple(Expr::class, false, $subNode, ...$others);
    }

    /**
     * Add a sub node which is an array of expressions.
     *
     * @param string $subNode The name of the expressions sub node.
     * @param string ...$others The names of further expressions sub nodes.
     * @return $this
     */
    public function withExprs(string $subNode = 'exprs', string ...$others): self
    {
        return $this->withMultiple(Expr::class, true, $subNode, ...$others);
    }

    /**
     * Add a sub node which is an array of function parameters. The name of the sub node is 'params'.
     *
     * @return $this
     */
    public function withParams(): self
    {
        return $this->with('params', Param::class, true);
    }

    /**
     * Add a sub node which is a return type. The name of the sub node is 'returnType'.
     *
     * @return $this
     */
    public function withReturn(): self
    {
        return $this->withType('returnType');
    }

    /**
     * Add a sub node which is an array of statements.
     *
     * @param string $subNode The name of the statements sub node.
     * @param string ...$others The names of further statements sub nodes.
     * @return $this
     */
    public function withStmts(string $subNode = 'stmts', string ...$others): self
    {
        return $this->withMultiple(Stmt::class, false, $subNode, ...$others);
    }



    /**
     * Add a sub node which is a type definition.
     *
     * @param string $subName The name of the sub node.
     * @return $this
     */
    public function withType(string $subName = 'type'): self
    {
        return $this->with(
            $subName,
            [SubNodeDefinition::NULL, Identifier::class, Name::class, ComplexType::class]
        );
    }
}