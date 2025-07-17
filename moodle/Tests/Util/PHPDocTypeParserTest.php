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
            'PHPDocTypes complex warn' => [
                'fixture' => 'phpdoctypes/phpdoctypes_complex_warn',
                'errors' => [],
                'warnings' => [
                    54 => "PHPDoc var type doesn't conform to PHP-FIG PHPDoc",
                    82 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    102 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    105 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    106 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    107 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    129 => "PHPDoc function parameter type doesn't conform to recommended style",
                    138 => "PHPDoc function parameter type doesn't conform to recommended style",
                    139 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    140 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    141 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    142 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    151 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    152 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    153 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    161 => "PHPDoc function parameter type doesn't conform to recommended style",
                    162 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    171 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    172 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    173 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    181 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    189 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    190 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    191 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    192 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    193 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    202 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    211 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    212 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    214 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    215 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    216 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    225 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    233 => "PHPDoc function return type doesn't conform to recommended style",
                    242 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    243 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    263 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    264 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    265 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    266 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    267 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    276 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    277 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    278 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    286 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    287 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    295 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    296 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    304 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    305 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    314 => "PHPDoc function return type doesn't conform to PHP-FIG PHPDoc",
                    322 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    323 => "PHPDoc function return type doesn't conform to PHP-FIG PHPDoc",
                    331 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    332 => "PHPDoc function return type doesn't conform to PHP-FIG PHPDoc",
                    340 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    341 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    342 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    343 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    344 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    345 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    346 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    347 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    356 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    358 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    375 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    378 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    379 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    380 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    384 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    385 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    388 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    443 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    460 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                    461 => "PHPDoc function parameter type doesn't conform to PHP-FIG PHPDoc",
                ],
            ],
            'PHPDocTypes parse wrong' => [
                'fixture' => 'phpdoctypes/phpdoctypes_parse_wrong',
                'errors' => [
                    41 => 'PHPDoc function parameter name missing or malformed',
                    49 => 'PHPDoc function parameter name missing or malformed',
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
                    119 => 'PHPDoc function parameter type missing or malformed',
                    126 => 'PHPDoc var type missing or malformed',
                    129 => 'PHPDoc var type missing or malformed',
                ],
                'warnings' => [],
            ],
            'PHPDocTypes simple right' => [
                'fixture' => 'phpdoctypes/phpdoctypes_simple_right',
                'errors' => [],
                'warnings' => [],
            ],
        ];
    }
}
