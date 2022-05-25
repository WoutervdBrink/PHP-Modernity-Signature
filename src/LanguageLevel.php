<?php

namespace Knevelina\Modernity;

enum LanguageLevel: string
{
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

    public function getMajor(): int
    {
        return substr($this->value, 0, strpos($this->value, '.'));
    }

    public function isOlderThan(LanguageLevel $other): bool
    {
        return \version_compare($this->value, $other->value, '<');
    }

    public function isNewerThan(LanguageLevel $other): bool
    {
        return \version_compare($this->value, $other->value, '>');
    }
}