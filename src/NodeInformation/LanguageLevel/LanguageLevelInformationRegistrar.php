<?php

namespace Knevelina\Modernity\NodeInformation\LanguageLevel;

use Knevelina\Modernity\Contracts\LanguageLevelInspector;
use Knevelina\Modernity\Contracts\NodeInformationRegistrar;
use Knevelina\Modernity\Enums\LanguageLevel;
use Knevelina\Modernity\NodeInformation\NodeInformationMapping;
use Knevelina\Modernity\Quirks;
use PhpParser\Node;

use function str_contains;

final class LanguageLevelInformationRegistrar implements NodeInformationRegistrar
{
    public static function map(NodeInformationMapping $mapping): void
    {
        self::mapExprInformation($mapping);
        self::mapNameInformation($mapping);
        self::mapScalarInformation($mapping);
        self::mapStmtInformation($mapping);
        self::mapOtherInformation($mapping);
    }

    private static function addMapping(
        NodeInformationMapping $mapping,
        string $class,
        LanguageLevelInspector $from = LanguageLevel::PHP5_2,
        ?LanguageLevelInspector $to = null
    ): void {
        $mapping->map($class, new LanguageLevelInformation($from, $to));
    }

    private static function mapExprInformation(NodeInformationMapping $mapping): void
    {
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\ArrayDimFetch::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\ArrayDimFetch $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/functionarraydereferencing
                    if ($node->var instanceof Node\Expr\CallLike) {
                        return LanguageLevel::PHP5_4;
                    }

                    // https://wiki.php.net/rfc/constdereference
                    if ($node->var instanceof Node\Expr\Array_ || $node->var instanceof Node\Scalar\String_) {
                        return LanguageLevel::PHP5_5;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\ArrayItem::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\Array_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\Array_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/shortsyntaxforarrays
                    if ($node->getAttribute('kind') === Node\Expr\Array_::KIND_SHORT) {
                        return LanguageLevel::PHP5_4;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\ArrowFunction::class, LanguageLevel::PHP7_4);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\Assign::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\Assign $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/short_list_syntax
                    if ($node->var instanceof Node\Expr\Array_) {
                        return LanguageLevel::PHP7_1;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\AssignOp::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\AssignRef::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\BinaryOp::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\BitwiseNot::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\BooleanNot::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Cast::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\ClassConstFetch::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\ClassConstFetch $node */ Node $node): ?LanguageLevel
                {
                    // https://www.php.net/manual/en/language.oop5.changelog.php
                    if ($node->name instanceof Node\Identifier && $node->name->name === 'class') {
                        return LanguageLevel::PHP5_5;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Clone_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Closure::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
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
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\ConstFetch::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\ConstFetch $node */ Node $node): ?LanguageLevel
                {
                    return match ($node->name->toString()) {
                        // https://wiki.php.net/rfc/e-user-deprecated-warning
                        'E_USER_DEPRECATED' => LanguageLevel::PHP5_3,
                        default => null
                    };
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\Empty_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\Empty_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/empty_isset_exprs
                    if (!$node->expr instanceof Node\Expr\Variable) {
                        return LanguageLevel::PHP5_5;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Error::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\ErrorSuppress::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Eval_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Exit_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\FuncCall::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Include_::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\Instanceof_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\Instanceof_ $node */ Node $node): ?LanguageLevel
                {
                    // As of PHP 8.0.0, instanceof can now be used with arbitrary expressions.
                    // https://www.php.net/instanceof
                    if ($node->class instanceof Node\Expr && !$node->class instanceof Node\Expr\Variable) {
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
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\Isset_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\Isset_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/empty_isset_exprs
                    foreach ($node->vars as $var) {
                        if ($var instanceof Node\Expr\Variable) {
                            return LanguageLevel::PHP5_5;
                        }
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\List_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\List_ $node */ Node $node): ?LanguageLevel
                {
                    /** @var Node\Expr\ArrayItem $item */
                    foreach ($node->items as $item) {
                        // https://wiki.php.net/rfc/list_reference_assignment
                        if ($item?->byRef) {
                            return LanguageLevel::PHP7_2;
                        }

                        // https://wiki.php.net/rfc/list_keys
                        if (!empty($item?->key)) {
                            return LanguageLevel::PHP7_1;
                        }
                    }
                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Match_::class, LanguageLevel::PHP8_0);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\MethodCall::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\MethodCall $node */ Node $node): ?LanguageLevel
                {
                    if (
                        $node->name instanceof Node\Expr\Variable &&
                        is_string($node->name->name) &&
                        Quirks::isSemiReservedKeyword($node->name->name)) {
                        return LanguageLevel::PHP7_0;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\New_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Expr\New_ $node */ Node $node): ?LanguageLevel
                {
                    // As of PHP 8.0.0, using new with arbitrary expressions is supported.
                    // https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.new
                    if ($node->class instanceof Node\Expr && !$node->class instanceof Node\Expr\Variable && !$node->class instanceof Node\Expr\PropertyFetch && !$node->class instanceof Node\Expr\ArrayDimFetch) {
                        return LanguageLevel::PHP8_0;
                    }

                    // https://www.php.net/manual/en/language.oop5.anonymous.php
                    if ($node->class instanceof Node\Stmt\Class_) {
                        return LanguageLevel::PHP7_0;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\NullsafeMethodCall::class,
            LanguageLevel::PHP8_0
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\NullsafePropertyFetch::class,
            LanguageLevel::PHP8_0
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\PostDec::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\PostInc::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\PreDec::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\PreInc::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Print_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\PropertyFetch::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\ShellExec::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\StaticCall::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\StaticPropertyFetch::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Ternary::class);

        // As of PHP 8.0.0, the throw keyword is an expression and may be used in any expression context.
        // https://www.php.net/manual/en/language.exceptions.php
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Throw_::class, LanguageLevel::PHP8_0);

        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\UnaryMinus::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\UnaryPlus::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Variable::class);
        // https://wiki.php.net/rfc/generator-delegation
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\YieldFrom::class, LanguageLevel::PHP7_0);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\Yield_::class, LanguageLevel::PHP5_5);

        // https://www.php.net/manual/en/language.types.null.php#language.types.null.casting
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\Cast\Unset_::class,
            to: LanguageLevel::PHP7_1
        );

        // https://wiki.php.net/rfc/pow-operator
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\AssignOp\Pow::class, LanguageLevel::PHP5_6);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Expr\BinaryOp\Pow::class, LanguageLevel::PHP5_6);

        // https://wiki.php.net/rfc/isset_ternary
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\AssignOp\Coalesce::class,
            LanguageLevel::PHP7_0
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\BinaryOp\Coalesce::class,
            LanguageLevel::PHP7_0
        );

        // https://wiki.php.net/rfc/combined-comparison-operator
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Expr\BinaryOp\Spaceship::class,
            LanguageLevel::PHP7_0
        );
    }

    private static function mapNameInformation(NodeInformationMapping $mapping): void
    {
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Name\FullyQualified::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Name\Relative::class, LanguageLevel::PHP5_3);
    }

    private static function mapScalarInformation(NodeInformationMapping $mapping): void
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
                if (!is_null($rawValue) && str_contains($rawValue, '_')) {
                    return LanguageLevel::PHP7_4;
                }

                // https://wiki.php.net/rfc/binnotation4ints
                if ($kind === Node\Scalar\LNumber::KIND_BIN) {
                    return LanguageLevel::PHP5_4;
                }

                return null;
            }
        };

        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Scalar\LNumber::class,
            from: $resolveNumericFrom,
            // https://wiki.php.net/rfc/octal.overload-checking
            to: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Scalar\LNumber $node */ Node $node): ?LanguageLevel
                {
                    if (
                        $node->getAttribute('kind') === Node\Scalar\LNumber::KIND_OCT &&
                        strlen($rawValue = $node->getAttribute('rawValue')) === 3 &&
                        in_array(substr($rawValue, 0, 1), ['4', '5', '6', '7'])
                    ) {
                        return LanguageLevel::PHP7_0;
                    }
                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Scalar\DNumber::class, from: $resolveNumericFrom);

        // __DIR__ and __NAMESPACE__ were added in 5.3.0
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Scalar\MagicConst\Dir::class,
            LanguageLevel::PHP5_3
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Scalar\MagicConst\Namespace_::class,
            LanguageLevel::PHP5_3
        );

        // __TRAIT__ was added in 5.4.0
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Scalar\MagicConst\Trait_::class,
            LanguageLevel::PHP5_4
        );

        // All other constants have been around forever - this is caught by defining Scalar as an always-superclass
        // later in registerOtherInformation().
    }

    private static function mapStmtInformation(NodeInformationMapping $mapping): void
    {
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Break_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Case_::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Catch_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Catch_ $node */ Node $node): ?LanguageLevel
                {
                    // The variable was required prior to PHP 8.0.0.
                    // https://www.php.net/manual/en/language.exceptions.php#language.exceptions.catch
                    if (empty($node->var)) {
                        return LanguageLevel::PHP8_0;
                    }

                    // https://wiki.php.net/rfc/multiple-catch
                    if (count($node->types) > 1) {
                        return LanguageLevel::PHP7_1;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\ClassConst::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\ClassConst $node */ Node $node): ?LanguageLevel
                {
                    // As of PHP 8.1.0, class constants can have the final modifier.
                    if ($node->flags & Node\Stmt\Class_::MODIFIER_FINAL) {
                        return LanguageLevel::PHP8_1;
                    }

                    // As of PHP 7.1.0 visibility modifiers are allowed for class constants.
                    // https://www.php.net/manual/en/language.oop5.constants.php
                    if (Quirks::flagsHaveVisibilityModifier($node->flags)) {
                        return LanguageLevel::PHP7_1;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\ClassMethod::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\ClassMethod $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/return_types
                    if (!empty($node->returnType)) {
                        $returnType = match (true) {
                            $node->returnType instanceof Node\Identifier, $node->returnType instanceof Node\Name => $node->returnType->toString(
                            ),
                            $node->returnType instanceof Node\ComplexType => '',
                        };

                        // https://wiki.php.net/rfc/noreturn_type
                        if ($returnType === 'noreturn') {
                            return LanguageLevel::PHP8_1;
                        }

                        // https://wiki.php.net/rfc/object-typehint
                        if ($returnType === 'object') {
                            return LanguageLevel::PHP7_2;
                        }

                        // https://wiki.php.net/rfc/void_return_type
                        // https://wiki.php.net/rfc/iterable
                        if ($returnType === 'void' || $returnType === 'iterable') {
                            return LanguageLevel::PHP7_1;
                        }

                        return LanguageLevel::PHP7_0;
                    }

                    // https://wiki.php.net/rfc/context_sensitive_lexer
                    if (Quirks::isSemiReservedKeyword($name = (string)$node->name->name)) {
                        return LanguageLevel::PHP7_0;
                    }

                    // https://www.php.net/manual/en/language.oop5.changelog.php
                    return match ($name) {
                        '__invoke', '__callStatic' => LanguageLevel::PHP5_3,
                        '__debugInfo' => LanguageLevel::PHP5_6,
                        '__serialize', '__unserialize' => LanguageLevel::PHP7_4,
                        default => null
                    };
                }
            },
            to: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\ClassMethod $node */ Node $node): ?LanguageLevel
                {
                    // https://www.php.net/manual/en/language.oop5.changelog.php
                    return match ($node->name->name) {
                        '__autoload' => LanguageLevel::PHP7_1,
                        default => null
                    };
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Class_::class,
            to: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Class_ $node */ Node $node): ?LanguageLevel
                {
                    // https://www.php.net/manual/en/language.oop5.changelog.php
                    return match ((string)$node->name?->name ?: '') {
                        'void', 'iterable' => LanguageLevel::PHP7_0,
                        'object' => LanguageLevel::PHP7_1,
                        default => null
                    };
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Const_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Const_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/new_in_initializers
                    foreach ($node->consts as $const) {
                        if ($const?->value instanceof Node\Expr\New_) {
                            return LanguageLevel::PHP8_1;
                        }
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Continue_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\DeclareDeclare::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Declare_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Do_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Echo_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\ElseIf_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Else_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\EnumCase::class, LanguageLevel::PHP8_1);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Enum_::class, LanguageLevel::PHP8_1);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Expression::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Finally_::class, LanguageLevel::PHP5_5);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\For_::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Foreach_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Foreach_ $node */ Node $node): ?LanguageLevel
                {
                    // It is possible to iterate over an array of arrays and unpack the nested array into loop variables
                    // by providing a list() as the value. (PHP 5 >= 5.5.0)
                    // https://www.php.net/manual/en/control-structures.foreach.php#control-structures.foreach.list
                    if ($node->valueVar instanceof Node\Expr\List_) {
                        return LanguageLevel::PHP5_5;
                    }

                    // https://wiki.php.net/rfc/short_list_syntax
                    if ($node->valueVar instanceof Node\Expr\Array_) {
                        return LanguageLevel::PHP7_1;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Function_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Function_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/return_types
                    if (!empty($node->returnType)) {
                        $returnType = match (true) {
                            $node->returnType instanceof Node\Identifier, $node->returnType instanceof Node\Name => $node->returnType->toString(
                            ),
                            $node->returnType instanceof Node\ComplexType => '',
                        };

                        // https://wiki.php.net/rfc/noreturn_type
                        if ($returnType === 'noreturn') {
                            return LanguageLevel::PHP8_1;
                        }

                        // https://wiki.php.net/rfc/object-typehint
                        if ($returnType === 'object') {
                            return LanguageLevel::PHP7_2;
                        }

                        // https://wiki.php.net/rfc/void_return_type
                        // https://wiki.php.net/rfc/iterable
                        if ($returnType === 'void' || $returnType === 'iterable') {
                            return LanguageLevel::PHP7_1;
                        }

                        return LanguageLevel::PHP7_0;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Global_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Goto_::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\GroupUse::class, LanguageLevel::PHP7_0);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\HaltCompiler::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\If_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\InlineHTML::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Interface_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Label::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Namespace_::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Nop::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Property::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Property $node */ Node $node): ?LanguageLevel
                {
                    // As of PHP 8.1.0, a property can be declared with the readonly modifier, which prevents
                    // modification of the property after initialization.
                    // https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.readonly-properties
                    if ($node->flags & Node\Stmt\Class_::MODIFIER_READONLY) {
                        return LanguageLevel::PHP8_1;
                    }

                    // As of PHP 7.4.0, property definitions can include a Type declarations, with the exception of callable.
                    // https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.typed-properties
                    if (!empty($node->type)) {
                        return LanguageLevel::PHP7_4;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\PropertyProperty::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Return_::class);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\StaticVar::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\StaticVar $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/new_in_initializers
                    if ($node->default instanceof Node\Expr\New_) {
                        return LanguageLevel::PHP8_1;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Static_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Switch_::class);
        // As of PHP 8.0.0, the throw keyword is an expression and may be used in any expression context.
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Throw_::class, to: LanguageLevel::PHP7_4);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\TraitUse::class, LanguageLevel::PHP5_4);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Trait_::class, LanguageLevel::PHP5_4);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\TryCatch::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\Unset_::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\UseUse::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\Use_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Use_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/use_function
                    if ($node->type === Node\Stmt\Use_::TYPE_FUNCTION) {
                        return LanguageLevel::PHP5_6;
                    }

                    return LanguageLevel::PHP5_3;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Stmt\While_::class);

        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\TraitUseAdaptation\Alias::class,
            LanguageLevel::PHP5_4
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Stmt\TraitUseAdaptation\Precedence::class,
            LanguageLevel::PHP5_4
        );
    }

    private static function mapOtherInformation(NodeInformationMapping $mapping): void
    {
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Arg::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Arg $node */ Node $node): ?LanguageLevel
                {
                    // As of PHP 8.0.0, named arguments can be used to skip over multiple optional parameters.
                    if (!empty($node->name)) {
                        return LanguageLevel::PHP8_0;
                    }

                    // https://wiki.php.net/rfc/argument_unpacking
                    if ($node->unpack) {
                        return LanguageLevel::PHP5_6;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Attribute::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Attribute $node */ Node $node): ?LanguageLevel
                {
                    foreach ($node->args as $arg) {
                        // https://wiki.php.net/rfc/new_in_initializers
                        if ($arg?->value instanceof Node\Expr\New_) {
                            return LanguageLevel::PHP8_1;
                        }
                    }

                    return LanguageLevel::PHP8_0;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\AttributeGroup::class, LanguageLevel::PHP8_0);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Const_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Const_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/context_sensitive_lexer
                    if (Quirks::isSemiReservedKeyword((string)$node->name)) {
                        return LanguageLevel::PHP7_0;
                    }

                    // https://wiki.php.net/rfc/const_scalar_exprs
                    if (!$node->value instanceof Node\Scalar) {
                        return LanguageLevel::PHP5_5;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Identifier::class);
        // https://wiki.php.net/rfc/pure-intersection-types
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\IntersectionType::class, LanguageLevel::PHP8_1);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\MatchArm::class, LanguageLevel::PHP8_0);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Name::class, LanguageLevel::PHP5_3);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\NullableType::class, LanguageLevel::PHP7_1);
        LanguageLevelInformationRegistrar::addMapping(
            $mapping,
            Node\Param::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Param $node */ Node $node): ?LanguageLevel
                {
                    // Default parameter values may be scalar values, arrays, the special type null, and as of
                    // PHP 8.1.0, objects using the new ClassName() syntax.
                    // https://www.php.net/manual/en/functions.arguments.php
                    if (!empty($node->default) && $node->default instanceof Node\Expr\New_) {
                        return LanguageLevel::PHP8_1;
                    }

                    if (!empty($node->type)) {
                        $type = match (true) {
                            $node->type instanceof Node\Identifier, $node->type instanceof Node\Name => $node->type->toString(
                            ),
                            $node->type instanceof Node\ComplexType => '',
                        };

                        // https://wiki.php.net/rfc/object-typehint
                        if ($type === 'object') {
                            return LanguageLevel::PHP7_2;
                        }

                        // https://wiki.php.net/rfc/iterable
                        if ($type === 'iterable') {
                            return LanguageLevel::PHP7_1;
                        }

                        // https://wiki.php.net/rfc/scalar_type_hints_v5
                        if (in_array($type, ['int', 'float', 'string', 'bool'])) {
                            return LanguageLevel::PHP7_0;
                        }

                        // https://wiki.php.net/rfc/callable
                        if ($type === 'callable') {
                            return LanguageLevel::PHP5_4;
                        }
                    }

                    if ($node->variadic) {
                        return LanguageLevel::PHP5_6;
                    }

                    return null;
                }
            }
        );
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\Scalar::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\UnionType::class, LanguageLevel::PHP8_0);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\VarLikeIdentifier::class);
        LanguageLevelInformationRegistrar::addMapping($mapping, Node\VariadicPlaceholder::class, LanguageLevel::PHP8_1);
    }
}