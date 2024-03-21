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
 * Test the PHPDocTypes sniff.
 *
 * @author     James Calder
 * @copyright  based on work by 2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Commenting\PHPDocTypesSniff
 */
class PHPDocTypesSniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider provider
     * @param string $fixture
     * @param array $errors
     * @param array $warnings
     */
    public function testPHPDocTypesCorrectness(
        string $fixture,
        array $errors,
        array $warnings
    ): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Commenting.PHPDocTypes');
        $this->setFixture(sprintf("%s/fixtures/%s.php", __DIR__, $fixture));
        $this->setWarnings($warnings);
        $this->setErrors($errors);
        /*$this->setApiMappings([
            'test' => [
                'component' => 'core',
                'allowspread' => true,
                'allowlevel2' => false,
            ],
        ]);*/

        $this->verifyCsResults();
    }

    /**
     * @return array
     */
    public static function provider(): array {
        return [
            'PHPDocTypes general right' => [
                'fixture' => 'phpdoctypes_general_right',
                'errors' => [],
                'warnings' => [],
            ],
            'PHPDocTypes method union types right' => [
                'fixture' => 'phpdoctypes_method_union_types_right',
                'errors' => [],
                'warnings' => [],
            ],
            'PHPDocTypes tags general right' => [
                'fixture' => 'phpdoctypes_tags_general_right',
                'errors' => [],
                'warnings' => [],
            ],
            'PHPDocTypes tags general wrong' => [
                'fixture' => 'phpdoctypes_tags_general_wrong',
                'errors' => [
                    41 => "PHPDoc function parameter 1 type missing or malformed",
                    42 => "PHPDoc function parameter 2 type missing or malformed",
                    48 => "PHPDoc number of function @param tags doesn't match actual number of parameters",
                    58 => "PHPDoc number of function @param tags doesn't match actual number of parameters",
                    65 => "PHPDoc number of function @param tags doesn't match actual number of parameters",
                    75 => "PHPDoc number of function @param tags doesn't match actual number of parameters",
                    88 => 'PHPDoc function parameter 2 type mismatch',
                    97 => 'PHPDoc function parameter 1 type mismatch',
                    107 => 'PHPDoc function parameter 1 type mismatch',
                    118 => 'PHPDoc function parameter 2 type mismatch',
                    127 => 'PHPDoc function return type missing or malformed',
                ],
                'warnings' => [
                    108 => 'PHPDoc function parameter 2 splat mismatch',
                ],
            ],
        ];
    }
}
