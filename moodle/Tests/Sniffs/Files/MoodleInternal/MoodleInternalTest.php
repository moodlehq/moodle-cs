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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Files\MoodleInternal;

/**
 * Test the MoodleInternalSniff sniff.
 *
 * @copyright  2013 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Files\MoodleInternalSniff
 */
class MoodleInternalTest extends \MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase
{
    protected function tearDown(): void {
        $this->resetMoodleRootCache();
        parent::tearDown();
    }

    /**
     * The sniff also has special cases which only apply when a Moodle root is detected.
     *
     * @dataProvider moodleRootPathDispensationsProvider
     */
    public function testMoodleRootPathDispensations(string $relativepath): void {
        $this->resetMoodleRootCache();

        $root = realpath(__DIR__ . '/../../../Util/fixtures/moodleutil/good');
        $this->assertNotFalse($root);
        $this->addCustomConfig('moodleRoot', $root);
        $this->assertSame($root, \MoodleHQ\MoodleCS\moodle\Util\MoodleUtil::getMoodleRoot(null, false));

        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.MoodleInternal');
        $this->setFixture(
            __DIR__ . '/../fixtures/MoodleInternal/problem.php',
            $root . '/' . $relativepath
        );
        $this->setWarnings([]);
        $this->setErrors([]);

        $this->verifyCsResults();
    }

    private function resetMoodleRootCache(): void {
        $reflection = new \ReflectionClass(\MoodleHQ\MoodleCS\moodle\Util\MoodleUtil::class);
        $moodleroot = $reflection->getProperty('moodleRoot');
        $moodleroot->setAccessible(true);
        $moodleroot->setValue(null, false);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function moodleRootPathDispensationsProvider(): array {
        return [
            'nested behat tests' => ['mod/example/tests/behat/steps/behat_example.php'],
            'nested behat library' => ['mod/example/lib/behat/steps/behat_example.php'],
            'nested language files' => ['mod/example/lang/en/subdirectory/example.php'],
        ];
    }

    /**
     * @dataProvider moodleFilesMoodleInternalProvider
     */
    public function testMoodleFilesMoodleInternal(
        string $fixture,
        array $warnings,
        array $errors
    ) {
        // Contains class_alias, which is not a side-effect.
        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.MoodleInternal');
        $this->setFixture(__DIR__ . '/../fixtures/MoodleInternal/' . $fixture . '.php');
        $this->setWarnings($warnings);
        $this->setErrors($errors);

        $this->verifyCsResults();
    }

    /**
     * Data provider for testMoodleFilesMoodleInternal tests.
     * @return array
     */
    public static function moodleFilesMoodleInternalProvider(): array {
        return [
            'conditional symbol declarations' => [
                'conditional_symbols',
                [],
                [],
            ],
            'conditional executable side effects' => [
                'conditional_side_effects',
                [],
                [3 => 'MoodleInternalGlobalState'],
            ],
            'declarations after the first symbol' => [
                'later_declarations',
                [],
                [],
            ],
            'anonymous class instantiation changes global state' => [
                'anonymous_side_effect',
                [],
                [3 => 'MoodleInternalGlobalState'],
            ],
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
