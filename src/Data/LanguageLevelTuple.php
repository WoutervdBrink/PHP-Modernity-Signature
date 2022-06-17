<?php

namespace Knevelina\Modernity\Data;

use ArrayAccess;
use BadMethodCallException;
use DomainException;
use Iterator;
use Knevelina\Modernity\Enums\LanguageLevel;
use OutOfBoundsException;
use Stringable;

use function array_key_exists;
use function is_float;
use function is_int;

final class LanguageLevelTuple implements ArrayAccess, Iterator, Stringable
{
    /** @var array<int|float> */
    private array $values;

    /** @var int Key for Iterator interface */
    private int $key;

    private static function getKey(mixed $level): string
    {
        if (!$level instanceof LanguageLevel) {
            throw new OutOfBoundsException('Level should be an instance of LanguageLevel');
        }

        return $level->name;
    }

    public function __construct()
    {
        static $initial;

        if (!isset($initial)) {
            $initial = [];
            foreach (LanguageLevel::range() as $level) {
                $initial[self::getKey($level)] = 0;
            }
        }

        $this->values = $initial;

        $this->key = 0;
    }

    public function add(LanguageLevelTuple $other): LanguageLevelTuple
    {
        $new = new LanguageLevelTuple();

        foreach (LanguageLevel::cases() as $level) {
            $new[$level] = $this[$level] + $other[$level];
        }

        return $new;
    }

    public function scale(int|float $factor): LanguageLevelTuple
    {
        $new = new LanguageLevelTuple();

        foreach (LanguageLevel::cases() as $level) {
            $new[$level] = $this[$level] * $factor;
        }

        return $new;
    }

    public function normalize(): LanguageLevelTuple
    {
        $max = array_reduce($this->values, fn(float $max, int|float $value) => max($max, $value), 0);

        return $max > 0
            ? $this->scale(1 / $max)
            : $this->scale(1);
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists(self::getKey($offset), $this->values);
    }

    public function offsetGet(mixed $offset): int|float
    {
        return $this->values[self::getKey($offset)];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!is_int($value) && !is_float($value)) {
            throw new DomainException('Language level tuple values should be integers or floats');
        }
        $this->values[self::getKey($offset)] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('Unsetting a language level tuple value is not supported');
    }

    public function current(): mixed
    {
        return $this->values[self::getKey(LanguageLevel::cases()[$this->key])];
    }

    public function next(): void
    {
        $this->key++;
    }

    public function key(): mixed
    {
        return LanguageLevel::cases()[$this->key];
    }

    public function valid(): bool
    {
        return isset(LanguageLevel::cases()[$this->key]);
    }

    public function rewind(): void
    {
        $this->key = 0;
    }

    public function __toString(): string
    {
        $parts = [];

        foreach ($this as $level => $value) {
            $parts[] = sprintf('%s: %.2f', $level->value, $value);
        }

        return sprintf('[%s]', implode(', ', $parts));
    }
}