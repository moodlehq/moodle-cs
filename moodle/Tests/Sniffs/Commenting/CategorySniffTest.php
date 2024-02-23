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
 * Test the CategorySniff sniff.
 *
 * @category   test
 * @copyright  2024 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Commenting\CategorySniff
 */
class CategorySniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider provider
     */
    public function test_category_correctness(
        string $fixture,
        array $errors,
        array $warnings
    ): void {
        $this->set_standard('moodle');
        $this->set_sniff('moodle.Commenting.Category');
        $this->set_fixture(sprintf("%s/fixtures/%s.php", __DIR__, $fixture));
        $this->set_warnings($warnings);
        $this->set_errors($errors);
        $this->set_api_mapping([
            'test' => [
                'component' => 'core',
                'allowspread' => true,
                'allowlevel2' => false,
            ],
        ]);

        $this->verify_cs_results();
    }

    public static function provider(): array {
        return [
            'Standard fixes' => [
                'fixture' => 'category_tags',
                'errors' => [
                    13 => 'Invalid @category tag value "core"',
                ],
                'warnings' => [],
            ],
        ];
    }
}
