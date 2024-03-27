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

namespace MoodleHQ\MoodleCS\moodle\Tests\Util;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Test the PHPDocTypeParser.
 *
 * @author     James Calder
 * @copyright  based on work by 2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Util\PHPDocTypeParser
 */
class PHPDocTypeParserTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider provider
     * @param string $fixture
     * @param array $errors
     * @param array $warnings
     */
    public function testPHPDocTypesParser(
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
            'PHPDocTypes all types right' => [
                'fixture' => 'phpdoctypes/phpdoctypes_all_types_right',
                'errors' => [],
                'warnings' => [
                    128 => "PHPDoc function parameter 1 type doesn't conform to recommended style",
                    136 => "PHPDoc function parameter 1 type doesn't conform to recommended style",
                ],
            ],
            'PHPDocTypes parse wrong' => [
                'fixture' => 'phpdoctypes/phpdoctypes_parse_wrong',
                'errors' => [
                    43 => 'PHPDoc function parameter 1 name missing or malformed',
                    50 => 'PHPDoc function parameter 1 name missing or malformed',
                    56 => 'PHPDoc var type missing or malformed',
                    59 => 'PHPDoc var type missing or malformed',
                    63 => 'PHPDoc var type missing or malformed',
                    67 => 'PHPDoc var type missing or malformed',
                    71 => 'PHPDoc var type missing or malformed',
                    74 => 'PHPDoc var type missing or malformed',
                    77 => 'PHPDoc var type missing or malformed',
                    80 => 'PHPDoc var type missing or malformed',
                    83 => 'PHPDoc var type missing or malformed',
                    86 => 'PHPDoc var type missing or malformed',
                    89 => 'PHPDoc var type missing or malformed',
                    93 => 'PHPDoc var type missing or malformed',
                    96 => 'PHPDoc var type missing or malformed',
                    99 => 'PHPDoc var type missing or malformed',
                    102 => 'PHPDoc var type missing or malformed',
                    105 => 'PHPDoc var type missing or malformed',
                    108 => 'PHPDoc var type missing or malformed',
                    111 => 'PHPDoc var type missing or malformed',
                    114 => 'PHPDoc var type missing or malformed',
                    119 => 'PHPDoc function parameter 1 type missing or malformed',
                    125 => 'PHPDoc var type missing or malformed',
                    128 => 'PHPDoc var type missing or malformed',
                ],
                'warnings' => [],
            ],
        ];
    }
}
