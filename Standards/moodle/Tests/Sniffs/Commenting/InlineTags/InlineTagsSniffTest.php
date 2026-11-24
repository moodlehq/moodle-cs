<?php

// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Commenting\InlineTags;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Tests inline PHPDoc tag validity, braces and contents.
 *
 * @copyright 2026 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Commenting\InlineTagsSniff
 */
class InlineTagsSniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider inlineTagsProvider
     * @param string $fixture The fixture name.
     */
    public function testInlineTags(string $fixture): void {
        $fixturePath = __DIR__ . '/fixtures/' . $fixture . '.php';
        $this->runTestsFromFixtureWithExpectations(
            $fixturePath,
            'moodle',
            'moodle.Commenting.InlineTags'
        );
    }

    public static function inlineTagsProvider(): array {
        return [
            'valid tags and standalone tags' => ['valid'],
            'invalid tags, braces and contents' => ['invalid'],
        ];
    }
}
