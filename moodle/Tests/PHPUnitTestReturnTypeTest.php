<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace MoodleHQ\MoodleCS\moodle\Tests;

// phpcs:disable moodle.NamingConventions

/**
 * Test the PHPUnitTestReturnTypeSniff sniff.
 *
 * @package    local_codechecker
 * @category   test
 * @copyright  2022 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\PHPUnit\TestReturnTypeSniff
 */
class PHPUnitTestReturnTypeTest extends MoodleCSBaseTestCase {

    /**
     * Data provider for self::provider_phpunit_data_returntypes
     */
    public function provider_phpunit_data_returntypes(): array {
        return [
            'Provider Casing' => [
                'fixture' => 'fixtures/phpunit/TestReturnType/returntypes.php',
                'errors' => [
                ],
                'warnings' => [
                    6 => 'Test method test_one() is missing a return type',
                    27 => 'Test method test_with_a_return() is missing a return type',
                    32 => 'Test method test_with_another_return() is missing a return type',
                    38 => 'Test method test_with_empty_return() is missing a return type',
                ],
            ],
        ];
    }

    /**
     * Test the moodle.PHPUnit.TestCaseCovers sniff
     *
     * @param string $fixture relative path to fixture to use.
     * @param array $errors array of errors expected.
     * @param array $warnings array of warnings expected.
     * @dataProvider provider_phpunit_data_returntypes
     */
    public function test_phpunit_test_returntypes(
        string $fixture,
        array $errors,
        array $warnings
    ): void {
        // Define the standard, sniff and fixture to use.
        $this->set_standard('moodle');
        $this->set_sniff('moodle.PHPUnit.TestReturnType');
        $this->set_fixture(__DIR__ . '/' . $fixture);

        // Define expected results (errors and warnings). Format, array of:
        // - line => number of problems,  or
        // - line => array of contents for message / source problem matching.
        // - line => string of contents for message / source problem matching (only 1).
        $this->set_errors($errors);
        $this->set_warnings($warnings);

        // Let's do all the hard work!
        $this->verify_cs_results();
    }
}
