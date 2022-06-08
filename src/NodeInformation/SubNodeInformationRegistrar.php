<?php

namespace Knevelina\Modernity\NodeInformation;

use Knevelina\Modernity\Contracts\NodeInformationRegistrar;
use PhpParser\Builder\EnumCase;
use PhpParser\Node;

class SubNodeInformationRegistrar implements NodeInformationRegistrar
{
    public static function map(NodeInformationMapping $mapping): void
    {
        self::mapExprInformation($mapping);
        self::mapNameInformation($mapping);
        self::mapScalarInformation($mapping);
        self::mapStmtInformation($mapping);
        self::mapOtherInformation($mapping);
    }

    private static function addMapping(NodeInformationMapping $mapping, string $class): SubNodeInformation
    {
        $mapping->map($class, $info = new SubNodeInformation($mapping));

        return $info;
    }

    private static function mapExprInformation(NodeInformationMapping $mapping)
    {
        self::addMapping($mapping, Node\Expr\ArrayDimFetch::class)
            ->withExpr('var')
            ->with('dim', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Expr\ArrayItem::class)
            ->with('key', Node\Expr::class, nullable: true)
            ->withExpr('value');

        self::addMapping($mapping, Node\Expr\Array_::class)
            ->with('items', Node\Expr\ArrayItem::class, true, true);

        self::addMapping($mapping, Node\Expr\ArrowFunction::class)
            ->withParams()
            ->withReturn()
            ->withExpr('expr')
            ->withAttrGroups();

        self::addMapping($mapping, Node\Expr\Assign::class)
            ->withExpr('var', 'expr');

        self::addMapping($mapping, Node\Expr\AssignOp::class)
            ->withExpr('var', 'expr');

        self::addMapping($mapping, Node\Expr\AssignRef::class)
            ->withExpr('var', 'expr');

        self::addMapping($mapping, Node\Expr\BinaryOp::class)
            ->withExpr('left', 'right');

        self::addMapping($mapping, Node\Expr\BitwiseNot::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\BooleanNot::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Cast::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\ClassConstFetch::class)
            ->with('class', [Node\Name::class, Node\Expr::class])
            ->with('name', Node\Identifier::class);

        self::addMapping($mapping, Node\Expr\Clone_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Closure::class)
            ->withParams()
            ->with('uses', Node\Expr\ClosureUse::class, true)
            ->withReturn()
            ->withStmts()
            ->withAttrGroups();

        self::addMapping($mapping, Node\Expr\ClosureUse::class)
            ->with('var', Node\Expr\Variable::class);

        self::addMapping($mapping, Node\Expr\ConstFetch::class)
            ->with('name', Node\Name::class);

        self::addMapping($mapping, Node\Expr\Empty_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\ErrorSuppress::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Eval_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Exit_::class)
            ->with('expr', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Expr\FuncCall::class)
            ->with('name', [Node\Name::class, Node\Expr::class])
            ->withArgs();

        self::addMapping($mapping, Node\Expr\Include_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Instanceof_::class)
            ->withExpr()
            ->with('class', [Node\Name::class, Node\Expr::class]);

        self::addMapping($mapping, Node\Expr\Isset_::class)
            ->withExprs('vars');

        self::addMapping($mapping, Node\Expr\List_::class)
            ->with('items', Node\Expr\ArrayItem::class, true, true);

        self::addMapping($mapping, Node\Expr\Match_::class)
            ->withExpr('cond')
            ->with('arms', Node\MatchArm::class, true);

        self::addMapping($mapping, Node\Expr\MethodCall::class)
            ->withExpr('var')
            ->with('name', [Node\Identifier::class, Node\Expr::class])
            ->withArgs();

        self::addMapping($mapping, Node\Expr\New_::class)
            ->with('class', [Node\Name::class, Node\Expr::class, Node\Stmt\Class_::class])
            ->withArgs();

        self::addMapping($mapping, Node\Expr\NullsafeMethodCall::class)
            ->withExpr('var')
            ->with('name', [Node\Identifier::class, Node\Expr::class])
            ->withArgs();

        self::addMapping($mapping, Node\Expr\NullsafePropertyFetch::class)
            ->withExpr('var')
            ->with('name', [Node\Identifier::class, Node\Expr::class]);

        self::addMapping($mapping, Node\Expr\PostDec::class)
            ->withExpr('var');

        self::addMapping($mapping, Node\Expr\PostInc::class)
            ->withExpr('var');

        self::addMapping($mapping, Node\Expr\PreDec::class)
            ->withExpr('var');

        self::addMapping($mapping, Node\Expr\PreInc::class)
            ->withExpr('var');

        self::addMapping($mapping, Node\Expr\Print_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\PropertyFetch::class)
            ->withExpr()
            ->with('name', [Node\Identifier::class, Node\Expr::class]);

        self::addMapping($mapping, Node\Expr\ShellExec::class);

        self::addMapping($mapping, Node\Expr\StaticCall::class)
            ->with('class', [Node\Name::class, Node\Expr::class])
            ->with('name', [Node\Identifier::class, Node\Expr::class])
            ->withArgs();

        self::addMapping($mapping, Node\Expr\StaticPropertyFetch::class)
            ->with('class', [Node\Name::class, Node\Expr::class])
            ->with('name', [Node\VarLikeIdentifier::class, Node\Expr::class]);

        self::addMapping($mapping, Node\Expr\Ternary::class)
            ->withExpr('cond', 'else')
            ->with('if', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Expr\Throw_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\UnaryMinus::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\UnaryPlus::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Variable::class)
            ->with('name', [SubNodeDefinition::STRING, Node\Expr::class]);

        self::addMapping($mapping, Node\Expr\YieldFrom::class)
            ->withExpr();

        self::addMapping($mapping, Node\Expr\Yield_::class)
            ->with('key', Node\Expr::class, nullable: true)
            ->with('value', Node\Expr::class, nullable: true);
    }

    private static function mapNameInformation(NodeInformationMapping $mapping)
    {
        self::addMapping($mapping, Node\Name\FullyQualified::class);
        self::addMapping($mapping, Node\Name\Relative::class);
    }

    private static function mapScalarInformation(NodeInformationMapping $mapping)
    {
        self::addMapping($mapping, Node\Scalar\DNumber::class)
            ->with('value', SubNodeDefinition::FLOAT);

        self::addMapping($mapping, Node\Scalar\Encapsed::class)
            ->withExprs('parts');

        self::addMapping($mapping, Node\Scalar\EncapsedStringPart::class)
            ->with('value', SubNodeDefinition::STRING);

        self::addMapping($mapping, Node\Scalar\LNumber::class)
            ->with('value', SubNodeDefinition::INT);

        self::addMapping($mapping, Node\Scalar\MagicConst::class);

        self::addMapping($mapping, Node\Scalar\String_::class)
            ->with('value', SubNodeDefinition::STRING);
    }

    private static function mapStmtInformation(NodeInformationMapping $mapping)
    {
        self::addMapping($mapping, Node\Stmt\Break_::class)
            ->with('num', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\Case_::class)
            ->with('cond', Node\Expr::class, nullable: true)
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\Catch_::class)
            ->with('types', Node\Name::class, true)
            ->with('var', Node\Expr\Variable::class, nullable: true)
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\ClassConst::class)
            ->with('consts', Node\Const_::class, true)
            ->withAttrGroups();

        self::addMapping($mapping, Node\Stmt\ClassLike::class)
            ->with('name', Node\Identifier::class, nullable: true)
            ->withStmts()
            ->withAttrGroups();

        self::addMapping($mapping, Node\Stmt\ClassMethod::class)
            ->with('name', Node\Identifier::class)
            ->withParams()
            ->withReturn()
            ->withStmts()
            ->withAttrGroups();

        self::addMapping($mapping, Node\Stmt\Class_::class)
            ->include(Node\Stmt\ClassLike::class)
            ->with('extends', Node\Name::class, nullable: true)
            ->with('implements', Node\Name::class, true);

        self::addMapping($mapping, Node\Stmt\Const_::class)
            ->with('consts', Node\Const_::class, true);

        self::addMapping($mapping, Node\Stmt\Continue_::class)
            ->with('num', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\DeclareDeclare::class)
            ->with('key', Node\Identifier::class)
            ->withExpr('value');

        self::addMapping($mapping, Node\Stmt\Declare_::class)
            ->with('declares', Node\Stmt\DeclareDeclare::class, true)
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\Do_::class)
            ->withStmts()
            ->withExpr('cond');

        self::addMapping($mapping, Node\Stmt\Echo_::class)
            ->withExprs('exprs');

        self::addMapping($mapping, Node\Stmt\ElseIf_::class)
            ->withExpr('cond')
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\Else_::class)
            ->withStmts();

        self::addMapping($mapping, EnumCase::class)
            ->with('name', Node\Identifier::class)
            ->with('expr', Node\Expr::class, nullable: true)
            ->withAttrGroups();

        self::addMapping($mapping, Node\Stmt\Enum_::class)
            ->with('scalarType', Node\Identifier::class, nullable: true)
            ->with('implements', Node\Name::class, true);

        self::addMapping($mapping, Node\Stmt\Expression::class)
            ->withExpr();

        self::addMapping($mapping, Node\Stmt\Finally_::class)
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\For_::class)
            ->withExprs('init', 'cond', 'loop')
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\Foreach_::class)
            ->withExpr('expr', 'valueVar')
            ->with('keyVar', Node\Expr::class, nullable: true)
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\Function_::class)
            ->with('name', Node\Identifier::class)
            ->withParams()
            ->withReturn()
            ->withStmts()
            ->withAttrGroups();

        self::addMapping($mapping, Node\Stmt\Global_::class)
            ->withExprs('vars');

        self::addMapping($mapping, Node\Stmt\Goto_::class)
            ->with('name', Node\Identifier::class);

        self::addMapping($mapping, Node\Stmt\GroupUse::class)
            ->with('prefix', Node\Name::class)
            ->with('uses', Node\Stmt\UseUse::class, true);

        self::addMapping($mapping, Node\Stmt\HaltCompiler::class)
            ->with('remaining', SubNodeDefinition::STRING);

        self::addMapping($mapping, Node\Stmt\If_::class)
            ->withExpr('cond')
            ->withStmts()
            ->with('elseifs', Node\Stmt\ElseIf_::class, true)
            ->with('else', Node\Stmt\Else_::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\InlineHTML::class)
            ->with('value', SubNodeDefinition::STRING);

        self::addMapping($mapping, Node\Stmt\Interface_::class)
            ->with('extends', Node\Name::class, true);

        self::addMapping($mapping, Node\Stmt\Label::class)
            ->with('name', Node\Identifier::class);

        self::addMapping($mapping, Node\Stmt\Namespace_::class)
            ->with('name', Node\Name::class, nullable: true)
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\Nop::class);

        self::addMapping($mapping, Node\Stmt\Property::class)
            ->with('props', Node\Stmt\PropertyProperty::class, true)
            ->withType()
            ->withAttrGroups();

        self::addMapping($mapping, Node\Stmt\PropertyProperty::class)
            ->with('name', Node\VarLikeIdentifier::class)
            ->with('default', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\Return_::class)
            ->with('expr', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\StaticVar::class)
            ->with('var', Node\Expr\Variable::class)
            ->with('default', Node\Expr::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\Static_::class)
            ->with('vars', Node\Stmt\StaticVar::class, true);

        self::addMapping($mapping, Node\Stmt\Switch_::class)
            ->withExpr('cond')
            ->with('cases', Node\Stmt\Case_::class, true);

        self::addMapping($mapping, Node\Stmt\Throw_::class)
            ->withExpr();

        self::addMapping($mapping, Node\Stmt\TraitUse::class)
            ->with('traits', Node\Name::class, true)
            ->with('adaptations', Node\Stmt\TraitUseAdaptation::class, true);

        self::addMapping($mapping, Node\Stmt\TraitUseAdaptation::class)
            ->with('trait', Node\Name::class, nullable: true)
            ->with('method', Node\Identifier::class);

        self::addMapping($mapping, Node\Stmt\Trait_::class)
            ->include(Node\Stmt\ClassLike::class);

        self::addMapping($mapping, Node\Stmt\TryCatch::class)
            ->withStmts()
            ->with('catches', Node\Stmt\Catch_::class, true)
            ->with('finally', Node\Stmt\Finally_::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\Unset_::class)
            ->withExprs('vars');

        self::addMapping($mapping, Node\Stmt\UseUse::class)
            ->with('name', Node\Name::class)
            ->with('alias', Node\Identifier::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\Use_::class)
            ->with('uses', Node\Stmt\UseUse::class, true);

        self::addMapping($mapping, Node\Stmt\While_::class)
            ->withExpr('cond')
            ->withStmts();

        self::addMapping($mapping, Node\Stmt\TraitUseAdaptation\Alias::class)
            ->include(Node\Stmt\TraitUseAdaptation::class)
            ->with('newModifier', SubNodeDefinition::INT, nullable: true)
            ->with('newName', Node\Identifier::class, nullable: true);

        self::addMapping($mapping, Node\Stmt\TraitUseAdaptation\Precedence::class)
            ->include(Node\Stmt\TraitUseAdaptation::class)
            ->with('insteadof', Node\Name::class, true);
    }

    private static function mapOtherInformation(NodeInformationMapping $mapping)
    {
        self::addMapping($mapping, Node\Arg::class)
            ->with('name', Node\Identifier::class, nullable: true)
            ->withExpr('value');

        self::addMapping($mapping, Node\Attribute::class)
            ->with('name', Node\Name::class)
            ->with('args', Node\Arg::class, true);

        self::addMapping($mapping, Node\AttributeGroup::class)
            ->with('attrs', Node\Attribute::class, true);

        self::addMapping($mapping, Node\Const_::class)
            ->with('name', Node\Identifier::class)
            ->withExpr('value')
            ->with('namespacedName', Node\Name::class, nullable: true);

        self::addMapping($mapping, Node\Identifier::class)
            ->with('name', SubNodeDefinition::STRING);

        self::addMapping($mapping, Node\IntersectionType::class)
            ->with('types', [Node\Identifier::class, Node\Name::class], true);

        self::addMapping($mapping, Node\MatchArm::class)
            ->with('conds', Node\Expr::class, true, true)
            ->withExpr('body');

        self::addMapping($mapping, Node\Name::class);

        self::addMapping($mapping, Node\NullableType::class)
            ->with('type', [Node\Identifier::class, Node\Name::class]);

        self::addMapping($mapping, Node\Param::class)
            ->withType()
            ->withExpr('var')
            ->with('default', Node\Expr::class, nullable: true)
            ->withAttrGroups();

        self::addMapping($mapping, Node\UnionType::class)
            ->with('types', [Node\Identifier::class, Node\Name::class], true);

        self::addMapping($mapping, Node\VarLikeIdentifier::class);

        self::addMapping($mapping, Node\VariadicPlaceholder::class);
    }
}