<?php

namespace Knevelina\Modernity\Tests;

use InvalidArgumentException;
use Knevelina\Modernity\Enums\LanguageLevel;
use PHPUnit\Framework\TestCase;

class LanguageLevelTest extends TestCase
{
    /**
     * @test
     * @dataProvider versions
     */
    public function it_has_major_versions(LanguageLevel $level, int $major): void
    {
        $this->assertEquals($major, $level->getMajor());
    }

    public function versions(): array
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
    public function it_compares_versions(LanguageLevel $older, LanguageLevel $newer): void
    {
        $this->assertTrue($older->isOlderThan($newer));
        $this->assertFalse($newer->isOlderThan($older));

        $this->assertTrue($newer->isNewerThan($older));
        $this->assertFalse($older->isNewerThan($older));
    }

    public function olderVersions(): array
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

    /** @test */
    public function it_creates_ranges_of_versions(): void
    {
        $this->assertEquals(
            [LanguageLevel::PHP5_2],
            LanguageLevel::range(LanguageLevel::PHP5_2, LanguageLevel::PHP5_2)
        );
        $this->assertEquals(
            [LanguageLevel::PHP5_2, LanguageLevel::PHP5_3],
            LanguageLevel::range(LanguageLevel::PHP5_2, LanguageLevel::PHP5_3)
        );
        $this->assertEquals(
            [LanguageLevel::PHP5_2, LanguageLevel::PHP5_3, LanguageLevel::PHP5_4],
            LanguageLevel::range(LanguageLevel::PHP5_2, LanguageLevel::PHP5_4)
        );
        $this->assertEquals(
            [
                LanguageLevel::PHP5_2,
                LanguageLevel::PHP5_3,
                LanguageLevel::PHP5_4,
                LanguageLevel::PHP5_5,
                LanguageLevel::PHP5_6,
                LanguageLevel::PHP7_0
            ],
            LanguageLevel::range(LanguageLevel::PHP5_2, LanguageLevel::PHP7_0)
        );
    }

    /** @test */
    public function it_will_not_create_ranges_when_start_is_newer_than_end(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('First language level 5.3 is newer than second language level 5.2');

        LanguageLevel::range(LanguageLevel::PHP5_3, LanguageLevel::PHP5_2);
    }
}