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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Commenting;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Test the TestCaseNamesSniff sniff.
 *
 * @copyright  2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Commenting\InlineCommentSniff
 */
class InlineCommentSniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider commentsProvider
     */
    public function testComments(
        string $fixture,
        ?string $fixtureFilename,
        array $errors,
        array $warnings
    ): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Commenting.InlineComment');
        $this->setFixture(sprintf("%s/fixtures/InlineComment/%s.php", __DIR__, $fixture), $fixtureFilename);
        $this->setWarnings($warnings);
        $this->setErrors($errors);
        $this->setComponentMapping([
            'local_codechecker' => dirname(__DIR__),
        ]);

        $this->verifyCsResults();
    }

    public static function commentsProvider(): \Generator {
        yield '' => [
            'fixture' => 'attributes',
            'fixtureFilename' => null,
            'errors' => [],
            'warnings' => [],
        ];
        yield 'Closing punctuation behaves correctly' => [
            'fixture' => 'punctuation',
            'fixtureFilename' => null,
            'errors' => [
            ],
            'warnings' => [
                38 => 'Inline comments must end in ',
                54 => 'Inline comments must end in ',
                61 => 'Inline comments must end in ',
                63 => 'Inline comments must end in ',
                65 => 'Inline comments must end in ',
            ],
        ];
    }
}
