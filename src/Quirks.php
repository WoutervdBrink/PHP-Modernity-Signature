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
        '_COOKIE',
        '_ENV',
        '_FILES',
        '_GET',
        '_POST',
        '_REQUEST',
        '_SERVER',
        '_SESSION',
    ];

    // https://wiki.php.net/rfc/context_sensitive_lexer
    private const SEMI_RESERVED_KEYWORDS = [
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'exit',
        'extends',
        'final',
        'finally',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'list',
        'namespace',
        'new',
        'or',
        'parent',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'self',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'use',
        'var',
        'while',
        'xor',
        'yield',
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
        return array_map(fn(Param $param): string => ((string)$param?->var?->name) ?? '', $params);
    }
}