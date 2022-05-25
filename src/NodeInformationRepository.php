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

use function array_key_exists;
use function str_contains;

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
    protected function register(NodeInformation $information): void
    {
        if (array_key_exists($class = $information->getClass(), $this->nodeMap)) {
            throw new InvalidArgumentException(
                sprintf('Version information on node "%s" has already been registered!', $class)
            );
        }

        $this->nodeMap[$class] = $information;
    }

    protected function registerWithCustomFrom(
        string $class,
        LanguageLevel $from = LanguageLevel::PHP5_2,
        ?LanguageLevel $to = null,
        ?callable $getFrom = null,
    ): void {
        $this->registerWithCustomFromTo($class, $from, $to, $getFrom);
    }

    protected function registerWithCustomTo(
        string $class,
        LanguageLevel $from = LanguageLevel::PHP5_2,
        ?LanguageLevel $to = null,
        ?callable $getTo = null,
    ) {
        $this->registerWithCustomFromTo($class, $from, $to, null, $getTo);
    }

    protected function registerWithCustomFromTo(
        string $class,
        LanguageLevel $from = LanguageLevel::PHP5_2,
        ?LanguageLevel $to = null,
        ?callable $getFrom = null,
        ?callable $getTo = null,
    ): void {
        $this->register(
            new class($class, $getFrom, $getTo, $from, $to) extends NodeInformation {
                private readonly ?\Closure $customGetFrom;
                private readonly ?\Closure $customGetTo;

                public function __construct(
                    string $class,
                    LanguageLevel $from,
                    ?LanguageLevel $to = null,
                    ?callable $customGetFrom = null,
                    ?callable $customGetTo = null
                ) {
                    parent::__construct($class, $from, $to);

                    $this->customGetFrom = $customGetFrom;
                    $this->customGetTo = $customGetTo;
                }

                public function getFrom(Node $node): LanguageLevel
                {
                    if (is_null($this->customGetFrom)) {
                        return parent::getFrom($node);
                    }

                    return empty($result = ($this->customGetFrom)($node)) ? parent::getFrom($node) : $result;
                }

                public function getTo(Node $node): ?LanguageLevel
                {
                    if (is_null($this->customGetTo)) {
                        return parent::getTo($node);
                    }

                    return empty($result = ($this->customGetTo)($node)) ? parent::getTo($node) : $result;
                }
            }
        );
    }

    protected function registerMultipleLevels(
        array $classes,
        LanguageLevel $from = LanguageLevel::PHP5_2,
        ?LanguageLevel $to = null
    ): void {
        foreach ($classes as $class) {
            $this->registerLevels($class, $from, $to);
        }
    }

    /**
     * Register a typical node, having only information about its versioning.
     *
     * @param string $class The class name of the node.
     * @param LanguageLevel $from The language level in which the node was introduced.
     * @param LanguageLevel|null $to The language level in which the node was removed and/or deprecated. If set to
     * <code>null</code>, the node is considered not-deprecated, i.e. we can use it in modern code.
     * @return void
     */
    protected function registerLevels(
        string $class,
        LanguageLevel $from = LanguageLevel::PHP5_2,
        ?LanguageLevel $to = null
    ): void {
        $this->register(new NodeInformation($class, $from, $to));
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
        $this->registerLevels(ArrayDimFetch::class);
        $this->registerLevels(ArrayItem::class);
        $this->registerLevels(Array_::class);
        $this->registerLevels(ArrowFunction::class, LanguageLevel::PHP7_4);
        $this->registerLevels(Assign::class);
        $this->registerLevels(AssignOp::class);
        $this->registerLevels(AssignRef::class);
        $this->registerLevels(BinaryOp::class);
        $this->registerLevels(BitwiseNot::class);
        $this->registerLevels(BooleanNot::class);
        $this->registerLevels(ClassConstFetch::class);
        $this->registerLevels(Clone_::class);
        $this->registerLevels(Closure::class, LanguageLevel::PHP5_3);
        $this->registerWithCustomTo(ClosureUse::class, getTo: function (ClosureUse $node): ?LanguageLevel {
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
        });
        $this->registerLevels(ConstFetch::class);
        $this->registerLevels(Empty_::class);
        $this->registerLevels(ErrorSuppress::class);
        $this->registerLevels(Eval_::class);
        $this->registerLevels(Exit_::class);
        $this->registerLevels(FuncCall::class);
        $this->registerLevels(Include_::class);
        $this->registerWithCustomFrom(Instanceof_::class, getFrom: function (Instanceof_ $node): ?LanguageLevel {
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
        });
        $this->registerLevels(Isset_::class);
        $this->registerLevels(List_::class);
        $this->registerLevels(Match_::class, LanguageLevel::PHP8_0);
        $this->registerLevels(MethodCall::class);
        $this->registerWithCustomFrom(New_::class, getFrom: function (New_ $node): ?LanguageLevel {
            // As of PHP 8.0.0, using new with arbitrary expressions is supported.
            // https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.new
            if ($node->class instanceof Expr) {
                return LanguageLevel::PHP8_0;
            }

            return null;
        });
        $this->registerLevels(NullsafeMethodCall::class, LanguageLevel::PHP8_0);
        $this->registerLevels(NullsafePropertyFetch::class, LanguageLevel::PHP8_0);
        $this->registerLevels(PostDec::class);
        $this->registerLevels(PostInc::class);
        $this->registerLevels(PreDec::class);
        $this->registerLevels(PreInc::class);
        $this->registerLevels(Print_::class);
        $this->registerLevels(PropertyFetch::class);
        $this->registerLevels(ShellExec::class);
        $this->registerLevels(StaticCall::class);
        $this->registerLevels(StaticPropertyFetch::class);
        $this->registerLevels(Ternary::class);

        // As of PHP 8.0.0, the throw keyword is an expression and may be used in any expression context.
        // https://www.php.net/manual/en/language.exceptions.php
        $this->registerLevels(Expr\Throw_::class, LanguageLevel::PHP8_0);

        $this->registerLevels(UnaryMinus::class);
        $this->registerLevels(UnaryPlus::class);
        $this->registerLevels(Variable::class);
        $this->registerLevels(YieldFrom::class, LanguageLevel::PHP5_5);
        $this->registerLevels(Yield_::class, LanguageLevel::PHP5_5);

        // TODO: Intelligently determine superclass during runtime using instanceof
        // TODO: Add tests for this!
        // See also BinaryOp, AssignOp, Cast expressions
    }

    protected function registerNameInformation()
    {
        $this->registerLevels(FullyQualified::class, LanguageLevel::PHP5_3);
        $this->registerLevels(Relative::class, LanguageLevel::PHP5_3);
    }

    protected function registerScalarInformation()
    {
        // Numeric values have some quirks.
        $resolveNumericFrom = function (LNumber|DNumber $node): ?LanguageLevel {
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
            if (str_contains($rawValue, '_')) {
                return LanguageLevel::PHP7_4;
            }

            return null;
        };

        $this->registerWithCustomFrom(LNumber::class, getFrom: $resolveNumericFrom);
        $this->registerWithCustomFrom(DNumber::class, getFrom: $resolveNumericFrom);

        // __DIR__ and __NAMESPACE__ were added in 5.3.0
        $this->registerLevels(MagicConst\Dir::class, LanguageLevel::PHP5_3);
        $this->registerLevels(MagicConst\Namespace_::class, LanguageLevel::PHP5_3);

        // __TRAIT__ was assed in 5.4.0
        $this->registerLevels(MagicConst\Trait_::class, LanguageLevel::PHP5_4);

        // All other constants have been around forever - this is caught by defining Scalar as an always-superclass
        // later in registerOtherInformation().
    }

    protected function registerStmtInformation()
    {
        $this->registerLevels(Break_::class);
        $this->registerLevels(Case_::class);
        $this->registerLevels(Catch_::class);
        $this->registerWithCustomFrom(ClassConst::class, getFrom: function (ClassConst $node): ?LanguageLevel {
            // As of PHP 7.1.0 visibility modifiers are allowed for class constants.
            // https://www.php.net/manual/en/language.oop5.constants.php
            if (Quirks::flagsHaveVisibilityModifier($node->flags)) {
                return LanguageLevel::PHP7_1;
            }

            return null;
        });
        // TODO: Finish :-)
    }

    protected function registerOtherInformation()
    {
        // TODO: Finish :-)
    }
}