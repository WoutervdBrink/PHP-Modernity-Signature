<?php

namespace Knevelina\Modernity\Enums;

use InvalidArgumentException;
use Knevelina\Modernity\Contracts\LanguageLevelInspector;
use PhpParser\Node;

use function usort;
use function version_compare;

enum LanguageLevel: string implements LanguageLevelInspector
{
    public const OLDEST = LanguageLevel::PHP5_2;
    public const NEWEST = LanguageLevel::PHP8_2;

    case PHP5_2 = '5.2';
    case PHP5_3 = '5.3';
    case PHP5_4 = '5.4';
    case PHP5_5 = '5.5';
    case PHP5_6 = '5.6';

    case PHP7_0 = '7.0';
    case PHP7_1 = '7.1';
    case PHP7_2 = '7.2';
    case PHP7_3 = '7.3';
    case PHP7_4 = '7.4';

    case PHP8_0 = '8.0';
    case PHP8_1 = '8.1';
    case PHP8_2 = '8.2';

    /**
     * Create an array containing a range of language levels.
     *
     * @param LanguageLevel $start First value of the sequence.
     * @param LanguageLevel $end Last value of the sequence, inclusive.
     * @return array<LanguageLevel>
     */
    public static function range(LanguageLevel $start = self::OLDEST, LanguageLevel $end = self::NEWEST): array
    {
        if ($start->isNewerThan($end)) {
            throw new InvalidArgumentException(
                sprintf('First language level %s is newer than second language level %s', $start->value, $end->value)
            );
        }
        $range = array_filter(
            LanguageLevel::cases(),
            fn(LanguageLevel $level): bool => (
                ($level === $start || $level->isNewerThan($start)) &&
                ($level === $end || $level->isOlderThan($end))
            )
        );

        usort(
            $range,
            fn(LanguageLevel $a, LanguageLevel $b): int => $a === $b ? 0 : ($a->isOlderThan($b) ? -1 : 1)
        );

        return $range;
    }

    public function getMajor(): int
    {
        return substr($this->value, 0, strpos($this->value, '.'));
    }

    public function isOlderThan(LanguageLevel $other): bool
    {
        return version_compare($this->value, $other->value, '<');
    }

    public function isNewerThan(LanguageLevel $other): bool
    {
        return version_compare($this->value, $other->value, '>');
    }

    public function inspect(Node $node): ?LanguageLevel
    {
        return $this;
    }
}