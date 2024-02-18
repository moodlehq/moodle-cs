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

// phpcs:disable moodle.NamingConventions

/**
 * Test the TestCaseNamesSniff sniff.
 *
 * @category   test
 * @copyright  2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Commenting\PackageSniff
 */
class PackageSniffTest extends MoodleCSBaseTestCase
{

    /**
     * @dataProvider package_correctness_provider
     */
    public function test_package_correctness(
        string $fixture,
        array $errors,
        array $warnings
    ): void {
        $this->set_standard('moodle');
        $this->set_sniff('moodle.Commenting.Package');
        $this->set_fixture(sprintf("%s/fixtures/%s.php", __DIR__, $fixture));
        $this->set_warnings($warnings);
        $this->set_errors($errors);
        $this->set_component_mapping([
            'local_codechecker' => dirname(__DIR__),
        ]);

        $this->verify_cs_results();
    }

    public static function package_correctness_provider(): array {
        return [
            'Standard fixes' => [
                'fixture' => 'package_tags',
                'errors' => [
                    18 => 'DocBlock missing a @package tag for function package_missing. Expected @package local_codechecker',
                    31 => 'DocBlock missing a @package tag for class package_absent. Expected @package local_codechecker',
                    34 => 'Missing doc comment for function missing_docblock_in_function',
                    38 => 'Missing doc comment for class missing_docblock_in_class',
                    42 => 'Incorrect @package tag for function package_wrong_in_function. Expected local_codechecker, found wrong_package.',
                    48 => 'Incorrect @package tag for class package_wrong_in_class. Expected local_codechecker, found wrong_package.',
                    57 => 'More than one @package tag found in function package_multiple_in_function',
                    64 => 'More than one @package tag found in class package_multiple_in_class',
                    71 => 'More than one @package tag found in function package_multiple_in_function_all_wrong',
                    78 => 'More than one @package tag found in class package_multiple_in_class_all_wrong',
                    85 => 'More than one @package tag found in interface package_multiple_in_interface_all_wrong',
                    92 => 'More than one @package tag found in trait package_multiple_in_trait_all_wrong',
                    95 => 'Missing doc comment for interface missing_docblock_interface',
                    101 => 'DocBlock missing a @package tag for interface missing_package_interface. Expected @package local_codechecker',
                    106 => 'Incorrect @package tag for interface incorrect_package_interface. Expected local_codechecker, found local_codecheckers.',
                    118 => 'Missing doc comment for trait missing_docblock_trait',
                    124 => 'DocBlock missing a @package tag for trait missing_package_trait. Expected @package local_codechecker',
                    129 => 'Incorrect @package tag for trait incorrect_package_trait. Expected local_codechecker, found local_codecheckers.',
                ],
                'warnings' => [],
            ],
            'File level tag (wrong)' => [
                'fixture' => 'package_tags_file_wrong',
                'errors' => [
                    20 => 'Incorrect @package tag for file package_tags_file_wrong.php. Expected local_codechecker, found core.',
                ],
                'warnings' => [],
            ],
            'File level tag (right)' => [
                'fixture' => 'package_tags_file_right',
                'errors' => [],
                'warnings' => [],
            ],
        ];
    }
}
