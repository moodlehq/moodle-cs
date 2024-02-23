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

namespace MoodleHQ\MoodleCS\moodle\Tests\Files;

// phpcs:disable moodle.NamingConventions

/**
 * Test the MoodleInternalSniff sniff.
 *
 * @package    local_codechecker
 * @category   test
 * @copyright  2013 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Files\MoodleInternalSniff
 */
class MoodleInternalTest extends \MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase {
    /**
     * @dataProvider provider
     */
    public function testFromProvider(
        string $fixture,
        array $warnings,
        array $errors
    ) {
        // Contains class_alias, which is not a side-effect.
        $this->set_standard('moodle');
        $this->set_sniff('moodle.Files.MoodleInternal');
        $this->set_fixture(__DIR__ . '/fixtures/moodleinternal/' . $fixture . '.php');
        $this->set_warnings($warnings);
        $this->set_errors($errors);

        $this->verify_cs_results();
    }

    /**
     * Data provider for MoodleInternal tests.
     * @return array
     */
    public static function provider(): array {
        return [
            [
                'problem',
                [],
                [
                    19 => 'Expected MOODLE_INTERNAL check or config.php inclusion',
                ],
            ],
            [
                'warning',
                [
                    32 => 'Expected MOODLE_INTERNAL check or config.php inclusion. Multiple artifacts',
                ],
                [],
            ],
            [
                'nowarning',
                [],
                [],
            ],
            [
                'declare_ok',
                [],
                [],
            ],
            [
                'enum_ok',
                [],
                [],
            ],
            [
                'namespace_ok',
                [],
                [],
            ],
            [
                'no_moodle_cookie_ok',
                [],
                [],
            ],
            [
                'tests/behat/behat_mod_workshop',
                [],
                [],
            ],
            [
                'lib/behat/behat_mod_workshop',
                [],
                [],
            ],
            [
                'lang/en/repository_dropbox',
                [],
                [],
            ],
            [
                'namespace_with_use_ok',
                [],
                [],
            ],
            [
                'old_style_if_die_ok',
                [
                    24 => 'Old MOODLE_INTERNAL check detected. Replace it by',
                ],
                [],
            ],
            [
                'no_relevant_ok',
                [],
                [],
            ],
            [
                'unexpected',
                [
                    17 => 'MoodleInternalNotNeeded',
                ],
                [],
            ],
            [
                'class_alias',
                [],
                [],
            ],
            [
                'class_alias_extra',
                [],
                [
                    25 => 'Expected MOODLE_INTERNAL check or config.php inclusion',
                ],
            ],
            [
                'class_alias_defined',
                [
                    17 => 'MoodleInternalNotNeeded',
                ],
                [],
            ],
            [
                'attribute_ok',
                [],
                [],
            ],
        ];
    }
}
