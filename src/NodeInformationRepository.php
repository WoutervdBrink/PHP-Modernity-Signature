<?php

namespace Knevelina\Modernity;

use InvalidArgumentException;
use PhpParser\Node;

use function array_key_exists;
use function str_contains;

final class NodeInformationRepository
{
    /**
     * @var array Mapping from class name to node information.
     */
    private array $nodeMap;

    public function __construct()
    {
        $this->nodeMap = [];

        $this->registerInformation();
    }

    /**
     * Register information about a node.
     *
     * @param string $class The class to register information about
     * @param LanguageLevelInspector $from The language level in which the node was introduced.
     * @param LanguageLevelInspector|null $to The language level in which the node was removed and/or deprecated.
     * @return void
     */
    protected function register(
        string $class,
        LanguageLevelInspector $from = LanguageLevel::PHP5_2,
        ?LanguageLevelInspector $to = null
    ): void {
        if (array_key_exists($class, $this->nodeMap)) {
            throw new InvalidArgumentException(
                sprintf('Version information on node "%s" has already been registered!', $class)
            );
        }

        $this->nodeMap[$class] = new NodeInformation($class, $from, $to);
    }

    protected function registerInformation()
    {
        $this->registerExprInformation();
        $this->registerNameInformation();
        $this->registerScalarInformation();
        $this->registerStmtInformation();
        $this->registerOtherInformation();
    }

    protected function registerExprInformation()
    {
        $this->register(Node\Expr\ArrayDimFetch::class);
        $this->register(Node\Expr\ArrayItem::class);
        $this->register(Node\Expr\Array_::class);
        $this->register(Node\Expr\ArrowFunction::class, LanguageLevel::PHP7_4);
        $this->register(Node\Expr\Assign::class);
        $this->register(Node\Expr\AssignOp::class);
        $this->register(Node\Expr\AssignRef::class);
        $this->register(Node\Expr\BinaryOp::class);
        $this->register(Node\Expr\BitwiseNot::class);
        $this->register(Node\Expr\BooleanNot::class);
        $this->register(Node\Expr\ClassConstFetch::class);
        $this->register(Node\Expr\Clone_::class);
        $this->register(Node\Expr\Closure::class, LanguageLevel::PHP5_3);
        $this->register(
                Node\Expr\ClosureUse::class,
            to: new class implements LanguageLevelInspector {
                    public function inspect(/** @var Node\Expr\ClosureUse $node */ Node $node): ?LanguageLevel
                    {
                        // "As of PHP 7.1, these variables must not include superglobals, $this, or variables with the same name
                        // as a parameter."
                        // https://www.php.net/manual/en/functions.anonymous.php
                        if (!is_string($var = $node->var->name)) {
                            return null;
                        }

                        if (Quirks::isSuperGlobal($var)) {
                            return LanguageLevel::PHP7_0;
                        }

                        /** @var Closure $parent */
                        $parent = $node->getAttribute('parent');
                        if (in_array($var, Quirks::getParameterVariableNames($parent->getParams()))) {
                            return LanguageLevel::PHP7_0;
                        }

                        return null;
                    }
                }
        );
        $this->register(Node\Expr\ConstFetch::class);
        $this->register(Node\Expr\Empty_::class);
        $this->register(Node\Expr\ErrorSuppress::class);
        $this->register(Node\Expr\Eval_::class);
        $this->register(Node\Expr\Exit_::class);
        $this->register(Node\Expr\FuncCall::class);
        $this->register(Node\Expr\Include_::class);
        $this->register(
                  Node\Expr\Instanceof_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\Instanceof_ $node */ Node $node): ?LanguageLevel
                      {
                          // As of PHP 8.0.0, instanceof can now be used with arbitrary expressions.
                          // https://www.php.net/instanceof
                          if ($node->class instanceof Node\Expr) {
                              return LanguageLevel::PHP8_0;
                          }

                          // As of PHP 7.3.0, constants are allowed on the left-hand-side of the instanceof operator.
                          // https://www.php.net/instanceof
                          if ($node->expr instanceof Node\Expr\ConstFetch) {
                              return LanguageLevel::PHP7_3;
                          }

                          return null;
                      }
                  }
        );
        $this->register(Node\Expr\Isset_::class);
        $this->register(Node\Expr\List_::class);
        $this->register(Node\Expr\Match_::class, LanguageLevel::PHP8_0);
        $this->register(Node\Expr\MethodCall::class);
        $this->register(
                  Node\Expr\New_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\New_ $node */ Node $node): ?LanguageLevel
                      {
                          // As of PHP 8.0.0, using new with arbitrary expressions is supported.
                          // https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.new
                          if ($node->class instanceof Node\Expr) {
                              return LanguageLevel::PHP8_0;
                          }

                          return null;
                      }
                  }
        );
        $this->register(Node\Expr\NullsafeMethodCall::class, LanguageLevel::PHP8_0);
        $this->register(Node\Expr\NullsafePropertyFetch::class, LanguageLevel::PHP8_0);
        $this->register(Node\Expr\PostDec::class);
        $this->register(Node\Expr\PostInc::class);
        $this->register(Node\Expr\PreDec::class);
        $this->register(Node\Expr\PreInc::class);
        $this->register(Node\Expr\Print_::class);
        $this->register(Node\Expr\PropertyFetch::class);
        $this->register(Node\Expr\ShellExec::class);
        $this->register(Node\Expr\StaticCall::class);
        $this->register(Node\Expr\StaticPropertyFetch::class);
        $this->register(Node\Expr\Ternary::class);

        // As of PHP 8.0.0, the throw keyword is an expression and may be used in any expression context.
        // https://www.php.net/manual/en/language.exceptions.php
        $this->register(Node\Expr\Throw_::class, LanguageLevel::PHP8_0);

        $this->register(Node\Expr\UnaryMinus::class);
        $this->register(Node\Expr\UnaryPlus::class);
        $this->register(Node\Expr\Variable::class);
        $this->register(Node\Expr\YieldFrom::class, LanguageLevel::PHP5_5);
        $this->register(Node\Expr\Yield_::class, LanguageLevel::PHP5_5);

        // TODO: Intelligently determine superclass during runtime using instanceof
        // TODO: Add tests for this!
        // See also BinaryOp, AssignOp, Cast expressions
    }

    protected function registerNameInformation()
    {
        $this->register(Node\Name\FullyQualified::class, LanguageLevel::PHP5_3);
        $this->register(Node\Name\Relative::class, LanguageLevel::PHP5_3);
    }

    protected function registerScalarInformation()
    {
        // Numeric values have some quirks.
        $resolveNumericFrom = new class implements LanguageLevelInspector {
            public function inspect(Node $node): ?LanguageLevel
            {
                // Default to decimal in case of floating point numbers.
                $kind = $node->getAttribute('kind', Node\Scalar\LNumber::KIND_DEC);
                $rawValue = $node->getAttribute('rawValue');

                // As of PHP 8.1.0, octal notation can also be preceded with 0o or 0O.
                // https://www.php.net/manual/en/language.types.integer.php
                if ($kind === Node\Scalar\LNumber::KIND_OCT && ($rawValue[1] === 'o' || $rawValue[1] === 'O')) {
                    return LanguageLevel::PHP8_1;
                }

                // As of PHP 7.4.0, integer literals may contain underscores (_) between digits, for better readability of
                // literals.
                // https://www.php.net/manual/en/language.types.integer.php
                // Also applies to floats: https://www.php.net/manual/en/language.types.float.php
                if (str_contains($rawValue, '_')) {
                    return LanguageLevel::PHP7_4;
                }

                return null;
            }
        };

        $this->register(Node\Scalar\LNumber::class, from: $resolveNumericFrom);
        $this->register(Node\Scalar\DNumber::class, from: $resolveNumericFrom);

        // __DIR__ and __NAMESPACE__ were added in 5.3.0
        $this->register(Node\Scalar\MagicConst\Dir::class, LanguageLevel::PHP5_3);
        $this->register(Node\Scalar\MagicConst\Namespace_::class, LanguageLevel::PHP5_3);

        // __TRAIT__ was added in 5.4.0
        $this->register(Node\Scalar\MagicConst\Trait_::class, LanguageLevel::PHP5_4);

        // All other constants have been around forever - this is caught by defining Scalar as an always-superclass
        // later in registerOtherInformation().
    }

    protected function registerStmtInformation()
    {
        $this->register(Node\Stmt\Break_::class);
        $this->register(Node\Stmt\Case_::class);
        $this->register(Node\Stmt\Catch_::class);
        $this->register(
                  Node\Stmt\ClassConst::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Stmt\ClassConst $node */ Node $node): ?LanguageLevel
                      {
                          // As of PHP 7.1.0 visibility modifiers are allowed for class constants.
                          // https://www.php.net/manual/en/language.oop5.constants.php
                          if (Quirks::flagsHaveVisibilityModifier($node->flags)) {
                              return LanguageLevel::PHP7_1;
                          }

                          return null;
                      }
                  }
        );
        // TODO: Finish :-)
    }

    protected function registerOtherInformation()
    {
        // TODO: Finish :-)
    }
}