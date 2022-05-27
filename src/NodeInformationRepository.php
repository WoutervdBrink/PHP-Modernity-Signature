<?php

namespace Knevelina\Modernity;

use InvalidArgumentException;
use PhpParser\Node;
use PhpParser\Node\{Expr,
    Expr\Array_,
    Expr\ArrayDimFetch,
    Expr\ArrayItem,
    Expr\ArrowFunction,
    Expr\Assign,
    Expr\AssignOp,
    Expr\AssignRef,
    Expr\BinaryOp,
    Expr\BitwiseNot,
    Expr\BooleanNot,
    Expr\ClassConstFetch,
    Expr\Clone_,
    Expr\Closure,
    Expr\ClosureUse,
    Expr\ConstFetch,
    Expr\Empty_,
    Expr\ErrorSuppress,
    Expr\Eval_,
    Expr\Exit_,
    Expr\FuncCall,
    Expr\Include_,
    Expr\Instanceof_,
    Expr\Isset_,
    Expr\List_,
    Expr\Match_,
    Expr\MethodCall,
    Expr\New_,
    Expr\NullsafeMethodCall,
    Expr\NullsafePropertyFetch,
    Expr\PostDec,
    Expr\PostInc,
    Expr\PreDec,
    Expr\PreInc,
    Expr\Print_,
    Expr\PropertyFetch,
    Expr\ShellExec,
    Expr\StaticCall,
    Expr\StaticPropertyFetch,
    Expr\Ternary,
    Expr\UnaryMinus,
    Expr\UnaryPlus,
    Expr\Variable,
    Expr\Yield_,
    Expr\YieldFrom,
    Name\FullyQualified,
    Name\Relative,
    Scalar\DNumber,
    Scalar\LNumber,
    Scalar\MagicConst,
    Stmt\Break_,
    Stmt\Case_,
    Stmt\Catch_,
    Stmt\ClassConst
};

final class NodeInformationRepository
{
    /**
     * @var array Mapping from class names to node informations.
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
     * @param NodeInformation $information Information to register.
     * @return void
     */
    protected function register(
        string $class,
        LanguageLevelInspector $from = LanguageLevel::PHP5_2,
        ?LanguageLevelInspector $to = null
    ): void {
        if (\array_key_exists($class, $this->nodeMap)) {
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
        $this->register(ArrayDimFetch::class);
        $this->register(ArrayItem::class);
        $this->register(Array_::class);
        $this->register(ArrowFunction::class, LanguageLevel::PHP7_4);
        $this->register(Assign::class);
        $this->register(AssignOp::class);
        $this->register(AssignRef::class);
        $this->register(BinaryOp::class);
        $this->register(BitwiseNot::class);
        $this->register(BooleanNot::class);
        $this->register(ClassConstFetch::class);
        $this->register(Clone_::class);
        $this->register(Closure::class, LanguageLevel::PHP5_3);
        $this->register(
                ClosureUse::class,
            to: new class implements LanguageLevelInspector {
                    public function inspect(Node $node): ?LanguageLevel
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
        $this->register(ConstFetch::class);
        $this->register(Empty_::class);
        $this->register(ErrorSuppress::class);
        $this->register(Eval_::class);
        $this->register(Exit_::class);
        $this->register(FuncCall::class);
        $this->register(Include_::class);
        $this->register(
                  Instanceof_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(Node $node): ?LanguageLevel
                      {
                          // As of PHP 8.0.0, instanceof can now be used with arbitrary expressions.
                          // https://www.php.net/instanceof
                          if ($node->class instanceof Expr) {
                              return LanguageLevel::PHP8_0;
                          }

                          // As of PHP 7.3.0, constants are allowed on the left-hand-side of the instanceof operator.
                          // https://www.php.net/instanceof
                          if ($node->expr instanceof ConstFetch) {
                              return LanguageLevel::PHP7_3;
                          }

                          return null;
                      }
                  }
        );
        $this->register(Isset_::class);
        $this->register(List_::class);
        $this->register(Match_::class, LanguageLevel::PHP8_0);
        $this->register(MethodCall::class);
        $this->register(
                  New_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(Node $node): ?LanguageLevel
                      {
                          // As of PHP 8.0.0, using new with arbitrary expressions is supported.
                          // https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.new
                          if ($node->class instanceof Expr) {
                              return LanguageLevel::PHP8_0;
                          }

                          return null;
                      }
                  }
        );
        $this->register(NullsafeMethodCall::class, LanguageLevel::PHP8_0);
        $this->register(NullsafePropertyFetch::class, LanguageLevel::PHP8_0);
        $this->register(PostDec::class);
        $this->register(PostInc::class);
        $this->register(PreDec::class);
        $this->register(PreInc::class);
        $this->register(Print_::class);
        $this->register(PropertyFetch::class);
        $this->register(ShellExec::class);
        $this->register(StaticCall::class);
        $this->register(StaticPropertyFetch::class);
        $this->register(Ternary::class);

        // As of PHP 8.0.0, the throw keyword is an expression and may be used in any expression context.
        // https://www.php.net/manual/en/language.exceptions.php
        $this->register(Expr\Throw_::class, LanguageLevel::PHP8_0);

        $this->register(UnaryMinus::class);
        $this->register(UnaryPlus::class);
        $this->register(Variable::class);
        $this->register(YieldFrom::class, LanguageLevel::PHP5_5);
        $this->register(Yield_::class, LanguageLevel::PHP5_5);

        // TODO: Intelligently determine superclass during runtime using instanceof
        // TODO: Add tests for this!
        // See also BinaryOp, AssignOp, Cast expressions
    }

    protected function registerNameInformation()
    {
        $this->register(FullyQualified::class, LanguageLevel::PHP5_3);
        $this->register(Relative::class, LanguageLevel::PHP5_3);
    }

    protected function registerScalarInformation()
    {
        // Numeric values have some quirks.
        $resolveNumericFrom = new class implements LanguageLevelInspector {
            public function inspect(Node $node): ?LanguageLevel
            {
                // Default to decimal in case of floating point numbers.
                $kind = $node->getAttribute('kind', LNumber::KIND_DEC);
                $rawValue = $node->getAttribute('rawValue');

                // As of PHP 8.1.0, octal notation can also be preceded with 0o or 0O.
                // https://www.php.net/manual/en/language.types.integer.php
                if ($kind === LNumber::KIND_OCT && ($rawValue[1] === 'o' || $rawValue[1] === 'O')) {
                    return LanguageLevel::PHP8_1;
                }

                // As of PHP 7.4.0, integer literals may contain underscores (_) between digits, for better readability of
                // literals.
                // https://www.php.net/manual/en/language.types.integer.php
                // Also applies to floats: https://www.php.net/manual/en/language.types.float.php
                if (\str_contains($rawValue, '_')) {
                    return LanguageLevel::PHP7_4;
                }

                return null;
            }
        };

        $this->register(LNumber::class, from: $resolveNumericFrom);
        $this->register(DNumber::class, from: $resolveNumericFrom);

        // __DIR__ and __NAMESPACE__ were added in 5.3.0
        $this->register(MagicConst\Dir::class, LanguageLevel::PHP5_3);
        $this->register(MagicConst\Namespace_::class, LanguageLevel::PHP5_3);

        // __TRAIT__ was assed in 5.4.0
        $this->register(MagicConst\Trait_::class, LanguageLevel::PHP5_4);

        // All other constants have been around forever - this is caught by defining Scalar as an always-superclass
        // later in registerOtherInformation().
    }

    protected function registerStmtInformation()
    {
        $this->register(Break_::class);
        $this->register(Case_::class);
        $this->register(Catch_::class);
        $this->register(
                  ClassConst::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(Node $node): ?LanguageLevel
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