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
            /*'PHPDocTypes docs missing wrong' => [
                'fixture' => 'phpdoctypes/phpdoctypes_docs_missing_wrong',
                'errors' => [],
                'warnings' => [
                    40 => "PHPDoc function is not documented",
                    43 => 2,
                    52 => "PHPDoc variable or constant is not documented",
                    54 => "PHPDoc variable missing @var tag",
                ],
            ],*/
            'PHPDocTypes general right' => [
                'fixture' => 'phpdoctypes/phpdoctypes_general_right',
                'errors' => [],
                'warnings' => [],
            ],
            'PHPDocTypes general wrong' => [
                'fixture' => 'phpdoctypes/phpdoctypes_general_wrong',
                'errors' => [
                    41 => "PHPDoc class property type missing or malformed",
                    42 => "PHPDoc class property name missing or malformed",
                    48 => "PHPDoc function parameter type missing or malformed",
                    49 => "PHPDoc function parameter name missing or malformed",
                    50 => "PHPDoc function parameter doesn't exist",
                    52 => "PHPDoc function parameter repeated",
                    53 => "PHPDoc function parameter type mismatch",
                    64 => "PHPDoc multiple function @return tags--Put in one tag, seperated by vertical bars |",
                    72 => "PHPDoc function return type missing or malformed",
                    79 => "PHPDoc function return type mismatch",
                    87 => "PHPDoc template name missing or malformed",
                    88 => "PHPDoc template type missing or malformed",
                    94 => "PHPDoc var type missing or malformed",
                    97 => "PHPDoc var type mismatch",
                    102 => "PHPDoc var type missing or malformed",
                ],
                'warnings' => [
                    31 => "PHPDoc misplaced tag",
                    46 => "PHPDoc function parameter order wrong",
                    54 => "PHPDoc function parameter splat mismatch",
                ],
            ],
            'PHPDocTypes method union types right' => [
                'fixture' => 'phpdoctypes/phpdoctypes_method_union_types_right',
                'errors' => [],
                'warnings' => [],
            ],
            'PHPDocTypes namespace right' => [
                'fixture' => 'phpdoctypes/phpdoctypes_namespace_right',
                'errors' => [],
                'warnings' => [],
            ],
            'PHPDocTypes parse wrong' => [
                'fixture' => 'phpdoctypes/phpdoctypes_parse_wrong',
                'errors' => [
                    91 => "PHPDoc function parameter type mismatch",
                ],
                'warnings' => [],
            ],
            'PHPDocTypes style wrong' => [
                'fixture' => 'phpdoctypes/phpdoctypes_style_wrong',
                'errors' => [],
                'warnings' => [
                    36 => "PHPDoc class property type doesn't conform to recommended style",
                    41 => "PHPDoc function parameter type doesn't conform to recommended style",
                    42 => "PHPDoc function return type doesn't conform to recommended style",
                    43 => "PHPDoc tempate type doesn't conform to recommended style",
                    49 => "PHPDoc var type doesn't conform to recommended style",
                    52 => "PHPDoc var type doesn't conform to recommended style",
                    56 => "PHPDoc var type doesn't conform to recommended style",
                    63 => "PHPDoc var type doesn't conform to recommended style",
                ],
            ],
            'PHPDocTypes tags general right' => [
                'fixture' => 'phpdoctypes/phpdoctypes_tags_general_right',
                'errors' => [],
                'warnings' => [],
            ],
        ];
    }
}
