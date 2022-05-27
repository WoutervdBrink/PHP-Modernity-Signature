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
    const SUPERGLOBALS = [
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