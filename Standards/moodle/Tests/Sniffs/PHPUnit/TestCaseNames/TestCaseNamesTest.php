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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\PHPUnit\TestCaseNames;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Test the TestCaseNamesSniff sniff.
 *
 * @copyright  2021 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\PHPUnit\TestCaseNamesSniff
 */
class TestCaseNamesTest extends MoodleCSBaseTestCase
{
    /**
     * Data provider for self::testPHPUnitTestCaseNamesProvider
     */
    public function phpunitTestCaseNamesProvider() {
        return [
            'Missing' => [
                'fixture' => 'testcasenames_missing.php',
                'errors' => [
                    7 => '@Message: PHPUnit test file missing any valid testcase class',
                    14 => 'Missing',
                ],
                'warnings' => [],
            ],
            'Irregular' => [
                'fixture' => 'testcasenames_test_testcase_irregular.php',
                'errors' => [
                    8 => '@Message: PHPUnit irregular testcase name found: testcasenames_test_testcase_irregular',
                ],
                'warnings' => [],
            ],
            'Abstract methods in a testcase' => [
                'fixture' => 'testcasenames_with_abstract_test.php',
                'errors' => [],
                'warnings' => [],
            ],
            'NoMatch' => [
                'fixture' => 'testcasenames_nomatch.php',
                'errors' => [],
                'warnings' => [
                    8 => '"testcasenames_nomatch_test" does not match file name "testcasenames_nomatch"',
                ],
            ],
            'UnexpectedNS' => [
                'fixture' => 'testcasenames_unexpected_ns.php',
                'errors' => [
                    2 => 'namespace "local_wrong" does not match expected file namespace "local_codechecker"',
                    8 => 1,
                ],
                'warnings' => [],
            ],
            'UnexpectedLevel2NS' => [
                'fixture' => 'testcasenames_unexpected_level2ns.php',
                'errors' => [
                    8 => 1,
                ],
                'warnings' => [
                    2 => 'does not match its expected location at "tests/level2/level3"',
                ],
            ],
            'CorrectLevel2NS' => [
                'fixture' => 'testcasenames_correct_level2ns.php',
                'errors' => [
                    8 => 1,
                ],
                'warnings' => [],
                'filename' => 'tests/fixtures/phpunit/testcasenames_correct_level2ns.php',
            ],
            'MismatchedLevel2NSLocation' => [
                'fixture' => 'testcasenames_correct_level2ns.php',
                'errors' => [
                    8 => 1,
                ],
                'warnings' => [
                    2 => 'does not match its expected location at "tests/fixtures/phpunit"',
                ],
                'filename' => 'tests/other/testcasenames_correct_level2ns.php',
            ],
            'MissingNS' => [
                'fixture' => 'testcasenames_missing_ns.php',
                'errors' => [
                    7 => 1,
                ],
                'warnings' => [
                    7 => 'add it to the "local_codechecker" namespace, using more levels',
                ],
            ],
            'DuplicateExists' => [
                'fixture' => 'testcasenames_duplicate_exists.php',
                'errors' => [
                    8 => [
                        'irregular testcase name found',
                        'testcasenames_duplicate_exists" already exists at "phpunit_fake_exists"',
                    ],
                ],
                'warnings' => [],
            ],
            'ProposedExists' => [
                'fixture' => 'testcasenames_proposed_exists.php',
                'errors' => [
                    8 => [
                        'irregular testcase name found',
                        'testcasenames_proposed_exists" already proposed for "phpunit_fake_proposed"',
                    ],
                ],
                'warnings' => [],
            ],
            'DuplicateProposed' => [
                'fixture' => 'testcasenames_duplicate_proposed.php',
                'errors' => [
                    7 => [
                        'irregular testcase name found',
                        'testcasenames_duplicate_proposed" already proposed for "phpunit_fake_proposed"',
                    ],
                ],
                'warnings' => [
                    7 => 1,
                ],
            ],
            'ExistsProposed' => [
                'fixture' => 'testcasenames_exists_proposed.php',
                'errors' => [
                    7 => [
                        'irregular testcase name found',
                        'testcasenames_exists_proposed" already exists at "phpunit_fake_exists"',
                    ],
                ],
                'warnings' => [
                    7 => 1,
                ],
            ],
        ];
    }

    /**
     * Test the moodle.PHPUnit.TestCaseNames sniff
     *
     * @param string $fixture relative path to fixture to use.
     * @param array $errors array of errors expected.
     * @param array $warnings array of warnings expected.
     * @param string|null $filename relative path to mock within the component.
     * @dataProvider phpunitTestCaseNamesProvider
     * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\PHPUnit\TestCaseNamesSniff
     */
    public function testPHPUnitTestCaseNames(string $fixture, array $errors, array $warnings, ?string $filename = null) {
        // Define the standard, sniff and fixture to use.
        $this->setStandard('moodle');
        $this->setSniff('moodle.PHPUnit.TestCaseNames');
        $fixtureFileName = $filename !== null ? dirname(__DIR__, 4) . '/' . $filename : null;
        $this->setFixture(__DIR__ . '/fixtures/' . $fixture, $fixtureFileName);
        $this->setComponentMapping([
            'local_codechecker' => dirname(__DIR__, 4),
        ]);

        // Define expected results (errors and warnings). Format, array of:
        // - line => number of problems,  or
        // - line => array of contents for message / source problem matching.
        // - line => string of contents for message / source problem matching (only 1).
        $this->setErrors($errors);
        $this->setWarnings($warnings);

        // Let's do all the hard work!
        $this->verifyCsResults();
    }
}
