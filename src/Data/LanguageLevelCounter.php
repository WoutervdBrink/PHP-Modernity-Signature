<?php

namespace Knevelina\Modernity\Data;

use Knevelina\Modernity\Enums\LanguageLevel;

class LanguageLevelCounter
{
    private LanguageLevelTuple $tuple;

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
        $this->tuple = new LanguageLevelTuple();
    }

    /**
     * Hit a sequence of language levels.
     *
     * @param LanguageLevel $start First value of the sequence.
     * @param LanguageLevel|null $end Last value of the sequence, inclusive.
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
        $this->tuple[$level] = $this->tuple[$level] + 1;
    }

    /**
     * Get the counter for a specified language level.
     *
     * @param LanguageLevel $level
     * @return int
     */
    public function get(LanguageLevel $level): int
    {
        return $this->tuple[$level];
    }

    /**
     * @return LanguageLevelTuple
     */
    public function getTuple(): LanguageLevelTuple
    {
        return $this->tuple;
    }
}