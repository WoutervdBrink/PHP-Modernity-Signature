<?php

namespace Knevelina\Modernity;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;

use function in_array;

/**
 * Support class to help with resolving certain versioning quirks.
 */
final class Quirks
{
    private const SUPERGLOBALS = [
        'GLOBALS',
        '_SERVER',
        '_GET',
        '_POST',
        '_FILES',
        '_COOKIE',
        '_SESSION',
        '_REQUEST',
        '_ENV',
    ];

    // https://wiki.php.net/rfc/context_sensitive_lexer
    private const SEMI_RESERVED_KEYWORDS = [
        'callable', 'class', 'trait', 'extends', 'implements', 'static', 'abstract', 'final', 'public', 'protected',
        'private', 'const', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endwhile', 'and', 'global', 'goto',
        'instanceof', 'insteadof', 'interface', 'namespace', 'new', 'or', 'xor', 'try', 'use', 'var', 'exit', 'list',
        'clone', 'include', 'include_once', 'throw', 'array', 'print', 'echo', 'require', 'require_once', 'return',
        'else', 'elseif', 'default', 'break', 'continue', 'switch', 'yield', 'function', 'if', 'endswitch', 'finally',
        'for', 'foreach', 'declare', 'case', 'do', 'while', 'as', 'catch', 'die', 'self', 'parent',
    ];

    public static function isSemiReservedKeyword(string $name): bool
    {
        return in_array($name, self::SEMI_RESERVED_KEYWORDS);
    }

    public static function isSuperGlobal(string $name): bool
    {
        return in_array($name, self::SUPERGLOBALS);
    }

    public static function flagsHaveVisibilityModifier(int $flag): bool
    {
        return $flag & (Class_::MODIFIER_PRIVATE | Class_::MODIFIER_PROTECTED | Class_::MODIFIER_PUBLIC);
    }

    public static function getParameterVariableNames(array $params): array
    {
        return array_map(fn(Param $param): string => (string)$param->var->name, $params);
    }
}