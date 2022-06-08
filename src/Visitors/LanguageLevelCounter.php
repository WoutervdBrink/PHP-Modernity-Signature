<?php

namespace Knevelina\Modernity\Visitors;

use Knevelina\Modernity\Enums\LanguageLevel;

class LanguageLevelCounter
{
    /** @var array<string, int> */
    private array $counters = [];

    /** @var int */
    private int $hits = 0;

    public function __construct()
    {
        $this->reset();
    }

    /**
     * Reset the counter.
     *
     * @return void
     */
    public function reset(): void
    {
        $this->hits = 0;

        foreach (LanguageLevel::cases() as $level) {
            $this->counters[$level->name] = 0;
        }
    }

    /**
     * Hit a sequence of language levels.
     *
     * @param LanguageLevel $start First value of the sequence.
     * @param LanguageLevel $end Last value of the sequence, inclusive.
     * @return void
     */
    public function hitRange(LanguageLevel $start, ?LanguageLevel $end): void
    {
        $range = LanguageLevel::range($start, $end);

        foreach ($range as $level) {
            $this->hit($level);
        }
    }

    /**
     * Hit the counter for a specified language level.
     *
     * @param LanguageLevel $level
     * @return void
     */
    public function hit(LanguageLevel $level): void
    {
        $this->counters[$level->name]++;
        $this->hits++;
    }

    /**
     * Get the counter for a specified language level.
     *
     * @param LanguageLevel $level
     * @return int
     */
    public function get(LanguageLevel $level): int
    {
        return $this->counters[$level->name];
    }

    /**
     * @return array<string, int>
     */
    public function getAll(): array
    {
        return $this->counters;
    }

    /**
     * @return int
     */
    public function getHits(): int
    {
        return $this->hits;
    }
}