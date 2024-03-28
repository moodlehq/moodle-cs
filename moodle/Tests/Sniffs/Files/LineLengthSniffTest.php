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
 * Test the MissingDocblockSniff sniff.
 *
 * @copyright  2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Files\LineLengthSniff
 */
class LineLengthSniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider fixtureProvider
     */
    public function testSniffWithFixtures(
        string $fixture,
        ?string $fixturePath,
        array $errors,
        array $warnings
    ): void {
        // xdebug_break();
        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.LineLength');
        $this->setFixture(
            sprintf("%s/fixtures/LineLength/%s.php", __DIR__, $fixture),
            $fixturePath,
        );
        $this->setErrors($errors);
        $this->setWarnings($warnings);

        $this->verifyCsResults();
    }

    public static function fixtureProvider(): array {
        $cases = [
            [
                'fixture' => 'langfile',
                'fixturePath' => '/lang/en/assignfeedback_editpdf.php',
                'errors' => [],
                'warnings' => [],
            ],
            [
                'fixture' => 'standard',
                'fixturePath' => null,
                'errors' => [
                    13 => 'Line exceeds maximum limit of 180 characters; contains 182 characters',
                ],
                'warnings' => [

                ],
            ],
        ];
        return $cases;
    }
}
