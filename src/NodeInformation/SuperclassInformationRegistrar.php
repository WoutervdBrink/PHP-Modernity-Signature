<?php

namespace Knevelina\Modernity\NodeInformation;

use Knevelina\Modernity\Contracts\NodeInformationRegistrar;
use PhpParser\Node;

/**
 * Mapping from AST nodes to their subclasses.
 *
 * This class is generated automatically using scripts/determine_subclasses.php.
 *
 * DO NOT MODIFY THIS FILE! Instead, modify the script.
 */
class SuperclassInformationRegistrar implements NodeInformationRegistrar
{

    public static function map(NodeInformationMapping $mapping): void
    {
         $mapping->map(Node\Arg::class, new SuperclassInformation(Node\Arg::class));
         $mapping->map(Node\Attribute::class, new SuperclassInformation(Node\Attribute::class));
         $mapping->map(Node\AttributeGroup::class, new SuperclassInformation(Node\AttributeGroup::class));
         $mapping->map(Node\ComplexType::class, new SuperclassInformation(Node\ComplexType::class));
         $mapping->map(Node\Const_::class, new SuperclassInformation(Node\Const_::class));
         $mapping->map(Node\Expr::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Identifier::class, new SuperclassInformation(Node\Identifier::class));
         $mapping->map(Node\IntersectionType::class, new SuperclassInformation(Node\ComplexType::class));
         $mapping->map(Node\MatchArm::class, new SuperclassInformation(Node\MatchArm::class));
         $mapping->map(Node\Name::class, new SuperclassInformation(Node\Name::class));
         $mapping->map(Node\NullableType::class, new SuperclassInformation(Node\ComplexType::class));
         $mapping->map(Node\Param::class, new SuperclassInformation(Node\Param::class));
         $mapping->map(Node\Scalar::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Stmt::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\UnionType::class, new SuperclassInformation(Node\ComplexType::class));
         $mapping->map(Node\VarLikeIdentifier::class, new SuperclassInformation(Node\Identifier::class));
         $mapping->map(Node\VariadicPlaceholder::class, new SuperclassInformation(Node\VariadicPlaceholder::class));
         $mapping->map(Node\Expr\ArrayDimFetch::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ArrayItem::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Array_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ArrowFunction::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Assign::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignRef::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BitwiseNot::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BooleanNot::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\CallLike::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ClassConstFetch::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Clone_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Closure::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ClosureUse::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ConstFetch::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Empty_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Error::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ErrorSuppress::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Eval_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Exit_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\FuncCall::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Include_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Instanceof_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Isset_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\List_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Match_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\MethodCall::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\New_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\NullsafeMethodCall::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\NullsafePropertyFetch::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\PostDec::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\PostInc::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\PreDec::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\PreInc::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Print_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\PropertyFetch::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\ShellExec::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\StaticCall::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\StaticPropertyFetch::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Ternary::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Throw_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\UnaryMinus::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\UnaryPlus::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Variable::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\YieldFrom::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Yield_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\BitwiseAnd::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\BitwiseOr::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\BitwiseXor::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Coalesce::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Concat::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Div::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Minus::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Mod::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Mul::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Plus::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\Pow::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\ShiftLeft::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\AssignOp\ShiftRight::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\BitwiseAnd::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\BitwiseOr::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\BitwiseXor::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\BooleanAnd::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\BooleanOr::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Coalesce::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Concat::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Div::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Equal::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Greater::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\GreaterOrEqual::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Identical::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\LogicalAnd::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\LogicalOr::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\LogicalXor::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Minus::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Mod::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Mul::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\NotEqual::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\NotIdentical::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Plus::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Pow::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\ShiftLeft::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\ShiftRight::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Smaller::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\SmallerOrEqual::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\BinaryOp\Spaceship::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\Array_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\Bool_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\Double::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\Int_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\Object_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\String_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Expr\Cast\Unset_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Name\FullyQualified::class, new SuperclassInformation(Node\Name::class));
         $mapping->map(Node\Name\Relative::class, new SuperclassInformation(Node\Name::class));
         $mapping->map(Node\Scalar\DNumber::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\Encapsed::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\EncapsedStringPart::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\LNumber::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\String_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Class_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Dir::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\File::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Function_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Line::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Method::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Namespace_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Scalar\MagicConst\Trait_::class, new SuperclassInformation(Node\Expr::class));
         $mapping->map(Node\Stmt\Break_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Case_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Catch_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\ClassConst::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\ClassLike::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\ClassMethod::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Class_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Const_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Continue_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\DeclareDeclare::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Declare_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Do_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Echo_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\ElseIf_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Else_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\EnumCase::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Enum_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Expression::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Finally_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\For_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Foreach_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Function_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Global_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Goto_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\GroupUse::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\HaltCompiler::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\If_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\InlineHTML::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Interface_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Label::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Namespace_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Nop::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Property::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\PropertyProperty::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Return_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\StaticVar::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Static_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Switch_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Throw_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\TraitUse::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\TraitUseAdaptation::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Trait_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\TryCatch::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Unset_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\UseUse::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\Use_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\While_::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\TraitUseAdaptation\Alias::class, new SuperclassInformation(Node\Stmt::class));
         $mapping->map(Node\Stmt\TraitUseAdaptation\Precedence::class, new SuperclassInformation(Node\Stmt::class));

    }
}