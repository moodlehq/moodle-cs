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
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Ruleset;

/**
 * Test the MissingDocblockSniff sniff.
 *
 * @copyright  2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Commenting\MissingDocblockSniff
 */
class MissingDocblockSniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider docblockCorrectnessProvider
     */
    public function testMissingDocblockSniff(
        string $fixture,
        array $errors,
        array $warnings
    ): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Commenting.MissingDocblock');
        $this->setFixture(sprintf("%s/fixtures/%s.php", __DIR__, $fixture));
        $this->setWarnings($warnings);
        $this->setErrors($errors);
        $this->setComponentMapping([
            'local_codechecker' => dirname(__DIR__),
        ]);

        $this->verifyCsResults();
    }

    public static function docblockCorrectnessProvider(): array {
        $cases = [
            'Multiple artifacts in a file' => [
                'fixture' => 'missing_docblock_multiple_artifacts',
                'errors' => [
                    1 => 'Missing docblock for file missing_docblock_multiple_artifacts.php',
                    34 => 'Missing docblock for function missing_docblock_in_function',
                    38 => 'Missing docblock for class missing_docblock_in_class',
                    95 => 'Missing docblock for interface missing_docblock_interface',
                    118 => 'Missing docblock for trait missing_docblock_trait',
                    151 => 'Missing docblock for function test_method2',
                    159 => 'Missing docblock for function test_method',
                    166 => 'Missing docblock for function test_method',
                    170 => 'Missing docblock for class example_extends',
                    175 => 'Missing docblock for class example_implements',
                ],
                'warnings' => [
                    171 => 'Missing docblock for function test_method',
                    176 => 'Missing docblock for function test_method',
                ],
            ],
            'File level tag, no class' => [
                'fixture' => 'missing_docblock_class_without_docblock',
                'errors' => [
                    11 => 'Missing docblock for class class_without_docblock',
                ],
                'warnings' => [],
            ],
            'Class only (incorrect whitespace)' => [
                'fixture' => 'missing_docblock_class_only_with_incorrect_whitespace',
                'errors' => [
                    11 => 'Missing docblock for class class_only_with_incorrect_whitespace',
                ],
                'warnings' => [],
            ],
            'Class only (correct)' => [
                'fixture' => 'missing_docblock_class_only',
                'errors' => [],
                'warnings' => [],
            ],
            'Class only with attributes (correct)' => [
                'fixture' => 'missing_docblock_class_only_with_attributes',
                'errors' => [],
                'warnings' => [],
            ],
            'Class only with attributes and incorrect whitespace' => [
                'fixture' => 'missing_docblock_class_only_with_attributes_incorrect_whitespace',
                'errors' => [
                    13 => 'Missing docblock for class class_only_with_attributes_incorrect_whitespace',
                ],
                'warnings' => [],
            ],
            'Class and file (correct)' => [
                'fixture' => 'missing_docblock_class_and_file',
                'errors' => [],
                'warnings' => [],
            ],
            'Interface only (correct)' => [
                'fixture' => 'missing_docblock_interface_only',
                'errors' => [],
                'warnings' => [],
            ],
            'Trait only (correct)' => [
                'fixture' => 'missing_docblock_trait_only',
                'errors' => [],
                'warnings' => [],
            ],
        ];

        if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
            $cases['Enum only (correct)'] = [
                'fixture' => 'missing_docblock_enum_only',
                'errors' => [],
                'warnings' => [],
            ];
        }

        return $cases;
    }

    public function testMissingDocblockSniffWithTest(): void {
        $content = <<<EOF
        phpcs_input_file: /lib/tests/example_test.php
        <?php

        class example_test extends advanced_testcase {
            public function setUp(): void {
            }
            public function tearDown(): void {
            }
            public static function setUpBeforeClass(): void {
            }
            public static function tearDownAfterClass(): void {
            }
            public function test_the_thing(): void {
            }
        }
        EOF;

        $this->setStandard('moodle');
        $this->setSniff('moodle.Commenting.MissingDocblock');
        $this->setFixtureFileContent($content);
        $this->setWarnings([
            12 => 'Missing docblock for function test_the_thing',
        ]);
        $this->setErrors([
            3 => 'Missing docblock for class example_test',
        ]);
        $this->setComponentMapping([
            'local_codechecker' => dirname(__DIR__),
        ]);

        $this->verifyCsResults();
    }
}
