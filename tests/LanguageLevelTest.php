<?php

use Knevelina\Modernity\LanguageLevel;
use PHPUnit\Framework\TestCase;

class LanguageLevelTest extends TestCase
{
    /**
     * @test
     * @dataProvider versions
     */
    function it_has_major_versions(LanguageLevel $level, int $major): void
    {
        $this->assertEquals($major, $level->getMajor());
    }

    public function versions()
    {
        return [
            [LanguageLevel::PHP5_2, 5],
            [LanguageLevel::PHP5_3, 5],
            [LanguageLevel::PHP5_4, 5],
            [LanguageLevel::PHP5_5, 5],
            [LanguageLevel::PHP5_6, 5],

            [LanguageLevel::PHP7_0, 7],
            [LanguageLevel::PHP7_1, 7],
            [LanguageLevel::PHP7_2, 7],
            [LanguageLevel::PHP7_3, 7],
            [LanguageLevel::PHP7_4, 7],

            [LanguageLevel::PHP8_0, 8],
            [LanguageLevel::PHP8_1, 8],
            [LanguageLevel::PHP8_2, 8],
        ];
    }

    /**
     * @test
     * @dataProvider olderVersions
     */
    function it_compares_versions(LanguageLevel $older, LanguageLevel $newer): void
    {
        $this->assertTrue($older->isOlderThan($newer));
        $this->assertFalse($newer->isOlderThan($older));

        $this->assertTrue($newer->isNewerThan($older));
        $this->assertFalse($older->isNewerThan($older));
    }

    public function olderVersions()
    {
        return [
            [LanguageLevel::PHP5_2, LanguageLevel::PHP5_3],
            [LanguageLevel::PHP5_3, LanguageLevel::PHP5_4],
            [LanguageLevel::PHP5_4, LanguageLevel::PHP5_5],
            [LanguageLevel::PHP5_5, LanguageLevel::PHP5_6],
            [LanguageLevel::PHP5_6, LanguageLevel::PHP7_0],
            [LanguageLevel::PHP7_0, LanguageLevel::PHP7_1],
            [LanguageLevel::PHP7_1, LanguageLevel::PHP7_2],
            [LanguageLevel::PHP7_2, LanguageLevel::PHP7_3],
            [LanguageLevel::PHP7_3, LanguageLevel::PHP7_4],
            [LanguageLevel::PHP7_4, LanguageLevel::PHP8_0],
            [LanguageLevel::PHP8_0, LanguageLevel::PHP8_1],
            [LanguageLevel::PHP8_1, LanguageLevel::PHP8_2],
        ];
    }
}