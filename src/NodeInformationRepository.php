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
        $this->register(
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
        $this->register(Node\Expr\ArrayItem::class);
        $this->register(
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
        $this->register(Node\Expr\ArrowFunction::class, LanguageLevel::PHP7_4);
        $this->register(
                  Node\Expr\Assign::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\Assign $node */ Node $node): ?LanguageLevel
                      {
                          // https://wiki.php.net/rfc/short_list_syntax
                          if ($node->var instanceof Node\Expr\Array_::class) {
                              return LanguageLevel::PHP7_1;
                          }

                          return null;
                      }
                  }
        );
        $this->register(Node\Expr\AssignOp::class);
        $this->register(Node\Expr\AssignRef::class);
        $this->register(Node\Expr\BinaryOp::class);
        $this->register(Node\Expr\BitwiseNot::class);
        $this->register(Node\Expr\BooleanNot::class);
        $this->register(
                  Node\Expr\ClassConstFetch::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\ClassConstFetch $node */ Node $node): ?LanguageLevel
                      {
                          // https://www.php.net/manual/en/language.oop5.changelog.php
                          if ((string)$node->name->name === 'class') {
                              return LanguageLevel::PHP5_5;
                          }

                          return null;
                      }
                  }
        );
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
        $this->register(
                  Node\Expr\ConstFetch::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\ConstFetch $node */ Node $node): ?LanguageLevel
                      {
                          return match ((string)$node->name) {
                              // https://wiki.php.net/rfc/e-user-deprecated-warning
                              'E_USER_DEPRECATED' => LanguageLevel::PHP5_3,
                              default => null
                          };
                      }
                  }
        );
        $this->register(
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
        $this->register(Node\Expr\ErrorSuppress::class);
        $this->register(Node\Expr\Eval_::class);
        $this->register(Node\Expr\Exit_::class);
        $this->register(
                  Node\Expr\FuncCall::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\FuncCall $node */ Node $node): ?LanguageLevel
                      {
                          // https://wiki.php.net/rfc/context_sensitive_lexer
                          if (Quirks::isSemiReservedKeyword((string)$node->name)) {
                              return LanguageLevel::PHP7_0;
                          }

                          return null;
                      }
                  }
        );
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
        $this->register(
                  Node\Expr\Isset_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\Isset_ $node */ Node $node): ?LanguageLevel
                      {
                          // https://wiki.php.net/rfc/empty_isset_exprs
                          if (!$node->expr instanceof Node\Expr\Variable) {
                              return LanguageLevel::PHP5_5;
                          }

                          return null;
                      }
                  }
        );
        $this->register(
                  Node\Expr\List_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\List_ $node */ Node $node): ?LanguageLevel
                      {
                          /** @var Node\Expr\ArrayItem $item */
                          foreach ($node->items as $item) {
                              // https://wiki.php.net/rfc/list_reference_assignment
                              if ($item->byRef) {
                                  return LanguageLevel::PHP7_2;
                              }

                              // https://wiki.php.net/rfc/list_keys
                              if (!empty($item->key)) {
                                  return LanguageLevel::PHP7_1;
                              }
                          }
                          return null;
                      }
                  }
        );
        $this->register(Node\Expr\Match_::class, LanguageLevel::PHP8_0);
        $this->register(
                  Node\Expr\MethodCall::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\MethodCall $node */ Node $node): ?LanguageLevel
                      {
                          if (Quirks::isSemiReservedKeyword((string)$node->name)) {
                              return LanguageLevel::PHP7_0;
                          }

                          return null;
                      }
                  }
        );
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

                          // https://www.php.net/manual/en/language.oop5.anonymous.php
                          if ($node->class instanceof Node\Stmt\Class_::class) {
                              return LanguageLevel::PHP7_0;
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
        $this->register(
                  Node\Expr\PropertyFetch::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Expr\PropertyFetch $node */ Node $node): ?LanguageLevel
                      {
                          // https://wiki.php.net/rfc/context_sensitive_lexer
                          if (Quirks::isSemiReservedKeyword((string)$node->name)) {
                              return LanguageLevel::PHP7_0;
                          }

                          return null;
                      }
                  }
        );
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
        // https://wiki.php.net/rfc/generator-delegation
        $this->register(Node\Expr\YieldFrom::class, LanguageLevel::PHP7_0);
        $this->register(Node\Expr\Yield_::class, LanguageLevel::PHP5_5);

        // https://www.php.net/manual/en/language.types.null.php#language.types.null.casting
        $this->register(Node\Expr\Cast\Unset_::class, to: LanguageLevel::PHP7_1);

        // https://wiki.php.net/rfc/pow-operator
        $this->register(Node\Expr\AssignOp\Pow::class, LanguageLevel::PHP5_6);
        $this->register(Node\Expr\BinaryOp\Pow::class, LanguageLevel::PHP5_6);

        // https://wiki.php.net/rfc/isset_ternary
        $this->register(Node\Expr\AssignOp\Coalesce::class, LanguageLevel::PHP7_0);
        $this->register(Node\Expr\BinaryOp\Coalesce::class, LanguageLevel::PHP7_0);

        // https://wiki.php.net/rfc/combined-comparison-operator
        $this->register(Node\Expr\BinaryOp\Spaceship::class, LanguageLevel::PHP7_0);

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

                // https://wiki.php.net/rfc/binnotation4ints
                if ($kind === Node\Scalar\LNumber::KIND_BIN) {
                    return LanguageLevel::PHP5_4;
                }

                return null;
            }
        };

        $this->register(
                  Node\Scalar\LNumber::class,
            from: $resolveNumericFrom,
            // https://wiki.php.net/rfc/octal.overload-checking
            to:   new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Scalar\LNumber $node */ Node $node): ?LanguageLevel
                      {
                          if (
                              $node->getAttribute('kind') === Node\Scalar\LNumber::KIND_OCT &&
                              strlen($rawValue = $node->getAttribute('rawValue')) === 3 &&
                              in_array(substr($node->getAttribute('rawValue'), 0, 1), ['4', '5', '6', '7'])
                          ) {
                              return LanguageLevel::PHP7_0;
                          }
                          return null;
                      }
                  }
        );
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
        $this->register(
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
        $this->register(
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
        $this->register(
                  Node\Stmt\ClassMethod::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Stmt\ClassMethod $node */ Node $node): ?LanguageLevel
                      {
                          // https://wiki.php.net/rfc/return_types
                          if (!empty($node->returnType)) {
                              $returnType = (string)$node->returnType;

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
            to:   new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Stmt\ClassMethod $node */ Node $node): ?LanguageLevel
                      {
                          // https://www.php.net/manual/en/language.oop5.changelog.php
                          return match ((string)$node->name->name) {
                              '__autoload' => LanguageLevel::PHP7_1,
                              default => null
                          };
                      }
                  }
        );
        $this->register(
                Node\Stmt\Class_::class,
            to: new class implements LanguageLevelInspector {
                    public function inspect(/** @var Node\Stmt\Class_ $node */ Node $node): ?LanguageLevel
                    {
                        // https://www.php.net/manual/en/language.oop5.changelog.php
                        return match ((string)$node->name->name) {
                            'void', 'iterable' => LanguageLevel::PHP7_0,
                            'object' => LanguageLevel::PHP7_1,
                            default => null
                        };
                    }
                }
        );
        $this->register(
            Node\Stmt\Const_::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\Const_ $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/new_in_initializers
                    foreach ($node->consts as $const) {
                        if ($const->value instanceof Node\Expr\New_::class) {
                            return LanguageLevel::PHP8_1;
                        }
                    }

                    return null;
                }
            }
        );
        $this->register(Node\Stmt\Continue_::class);
        $this->register(Node\Stmt\DeclareDeclare::class);
        $this->register(Node\Stmt\Declare_::class);
        $this->register(Node\Stmt\Do_::class);
        $this->register(Node\Stmt\Echo_::class);
        $this->register(Node\Stmt\ElseIf_::class);
        $this->register(Node\Stmt\Else_::class);
        $this->register(Node\Stmt\EnumCase::class, LanguageLevel::PHP8_1);
        $this->register(Node\Stmt\Enum_::class, LanguageLevel::PHP8_1);
        $this->register(Node\Stmt\Expression::class);
        $this->register(Node\Stmt\Finally_::class, LanguageLevel::PHP5_5);
        $this->register(Node\Stmt\For_::class);
        $this->register(
                  Node\Stmt\Foreach_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Stmt\Foreach_ $node */ Node $node): ?LanguageLevel
                      {
                          // It is possible to iterate over an array of arrays and unpack the nested array into loop variables
                          // by providing a list() as the value. (PHP 5 >= 5.5.0)
                          // https://www.php.net/manual/en/control-structures.foreach.php#control-structures.foreach.list
                          if ($node->valueVar instanceof Node\Expr\List_::class) {
                              return LanguageLevel::PHP5_5;
                          }

                          // https://wiki.php.net/rfc/short_list_syntax
                          if ($node->valueVar instanceof Node\Expr\Array_::class) {
                              return LanguageLevel::PHP7_1;
                          }

                          return null;
                      }
                  }
        );
        $this->register(
                  Node\Stmt\Function_::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Stmt\Function_ $node */ Node $node): ?LanguageLevel
                      {
                          // https://wiki.php.net/rfc/return_types
                          if (!empty($node->returnType)) {
                              $returnType = (string)$node->returnType;

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
        $this->register(Node\Stmt\Global_::class);
        $this->register(Node\Stmt\Goto_::class, LanguageLevel::PHP5_3);
        $this->register(Node\Stmt\GroupUse::class, LanguageLevel::PHP7_0);
        $this->register(Node\Stmt\HaltCompiler::class);
        $this->register(Node\Stmt\If_::class);
        $this->register(Node\Stmt\InlineHTML::class);
        $this->register(Node\Stmt\Interface_::class);
        $this->register(Node\Stmt\Label::class, LanguageLevel::PHP5_3);
        $this->register(Node\Stmt\Namespace_::class, LanguageLevel::PHP5_3);
        $this->register(Node\Stmt\Nop::class);
        $this->register(
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
        $this->register(Node\Stmt\PropertyProperty::class);
        $this->register(Node\Stmt\Return_::class);
        $this->register(
            Node\Stmt\StaticVar::class,
            from: new class implements LanguageLevelInspector {
                public function inspect(/** @var Node\Stmt\StaticVar $node */ Node $node): ?LanguageLevel
                {
                    // https://wiki.php.net/rfc/new_in_initializers
                    if ($node->default instanceof Node\Expr\New_::class) {
                        return LanguageLevel::PHP8_1;
                    }

                    return null;
                }
            }
        );
        $this->register(Node\Stmt\Static_::class);
        $this->register(Node\Stmt\Switch_::class);
        // As of PHP 8.0.0, the throw keyword is an expression and may be used in any expression context.
        $this->register(Node\Stmt\Throw_::class, to: LanguageLevel::PHP7_4);
        $this->register(Node\Stmt\TraitUse::class, LanguageLevel::PHP5_4);
        $this->register(Node\Stmt\Trait_::class, LanguageLevel::PHP5_4);
        $this->register(Node\Stmt\TryCatch::class);
        $this->register(Node\Stmt\Unset_::class);
        $this->register(Node\Stmt\UseUse::class, LanguageLevel::PHP5_3);
        $this->register(
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
        $this->register(Node\Stmt\While_::class);

        $this->register(Node\Stmt\TraitUseAdaptation\Alias::class, LanguageLevel::PHP5_4);
        $this->register(Node\Stmt\TraitUseAdaptation\Precedence::class, LanguageLevel::PHP5_4);
    }

    protected function registerOtherInformation()
    {
        $this->register(
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
        $this->register(
                  Node\Attribute::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Attribute $node */ Node $node): ?LanguageLevel
                      {
                          foreach ($node->args as $arg) {
                              // https://wiki.php.net/rfc/new_in_initializers
                              if ($arg->value instanceof Node\Expr\New_::class) {
                                  return LanguageLevel::PHP8_1;
                              }
                          }

                          return LanguageLevel::PHP8_0;
                      }
                  }
        );
        $this->register(Node\AttributeGroup::class, LanguageLevel::PHP8_0);
        $this->register(
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
        $this->register(Node\Identifier::class);
        // https://wiki.php.net/rfc/pure-intersection-types
        $this->register(Node\IntersectionType::class, LanguageLevel::PHP8_1);
        $this->register(Node\MatchArm::class, LanguageLevel::PHP8_0);
        $this->register(Node\Name::class, LanguageLevel::PHP5_3);
        $this->register(Node\NullableType::class, LanguageLevel::PHP7_1);
        $this->register(
                  Node\Param::class,
            from: new class implements LanguageLevelInspector {
                      public function inspect(/** @var Node\Param $node */ Node $node): ?LanguageLevel
                      {
                          // Default parameter values may be scalar values, arrays, the special type null, and as of
                          // PHP 8.1.0, objects using the new ClassName() syntax.
                          // https://www.php.net/manual/en/functions.arguments.php
                          if (!empty($node->default) && $node->default instanceof Node\Expr\New_::class) {
                              return LanguageLevel::PHP8_1;
                          }

                          if (!empty($node->type)) {
                              // https://wiki.php.net/rfc/object-typehint
                              if ($node->type === 'object') {
                                  return LanguageLevel::PHP7_2;
                              }

                              // https://wiki.php.net/rfc/iterable
                              if ($node->Type === 'iterable') {
                                  return LanguageLevel::PHP7_1;
                              }

                              // https://wiki.php.net/rfc/scalar_type_hints_v5
                              if (in_array((string)$node->type, ['int', 'float', 'string', 'bool'])) {
                                  return LanguageLevel::PHP7_0;
                              }
                          }

                          if ($node->variadic) {
                              return LanguageLevel::PHP5_6;
                          }

                          // https://wiki.php.net/rfc/callable
                          if (!empty($node->type) && (string)$node->type === 'callable') {
                              return LanguageLevel::PHP5_4;
                          }

                          return null;
                      }
                  }
        );
        $this->register(Node\UnionType::class, LanguageLevel::PHP8_0);
        $this->register(Node\VarLikeIdentifier::class);
        $this->register(Node\VariadicPlaceholder::class, LanguageLevel::PHP8_1);
    }
}