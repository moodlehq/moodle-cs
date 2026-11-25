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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Strings\PreferCoreText;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\LocalFile;
use PHP_CodeSniffer\Ruleset;

/**
 * Tests for PHP string function warnings.
 *
 * @copyright 2026 Cameron Ball <cameron@cameron1729.xyz>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Strings\PreferCoreTextSniff
 */
class PreferCoreTextTest extends MoodleCSBaseTestCase
{
    /**
     * Test function warnings.
     *
     * @dataProvider functionCallsProvider
     * @param string $fixture Fixture name.
     * @param array $warnings Expected warnings.
     */
    public function testFunctionWarnings(string $fixture, array $warnings): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Strings.PreferCoreText');
        $this->setFixture(__DIR__ . '/fixtures/' . $fixture . '.php');
        $this->setErrors([]);
        $this->setWarnings($warnings);
        $this->verifyCsResults();
    }

    /**
     * Test core_text suggestions.
     */
    public function testOtherCoreTextAlternatives(): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Strings.PreferCoreText');
        $this->setFixture(__DIR__ . '/fixtures/alternatives.php');
        $this->setErrors([]);
        $source = '@Source: moodle.Strings.PreferCoreText.';
        $this->setWarnings([
            3 => 'Consider core_text::strlen(). ' . $source . 'Strlen',
            4 => 'Consider core_text::strtolower(). ' . $source . 'Strtolower',
            5 => 'Consider core_text::strtoupper(). ' . $source . 'Strtoupper',
            6 => 'Consider core_text::strpos(). ' . $source . 'Strpos',
            7 => 'Consider core_text::strrpos(). ' . $source . 'Strrpos',
            8 => 'Consider core_text::strrchr(). ' . $source . 'Strrchr',
            9 => 'Consider core_text::strrev(). ' . $source . 'Strrev',
        ]);
        $this->verifyCsResults();
    }

    /**
     * Function call test cases.
     *
     * @return array
     */
    public static function functionCallsProvider(): array {
        $prefix = '@Source: moodle.Strings.PreferCoreText.';
        $source = $prefix . 'Substr';
        return [
            'direct calls, casing, named arguments and callable creation' => [
                'calls',
                [
                    3 => '@Message: Consider core_text::substr().',
                    4 => $source,
                    5 => $source,
                    6 => $source,
                    7 => $source,
                    8 => [$source, '@Source: moodle.Strings.PreferCoreText.Strlen'],
                    9 => $source,
                    10 => $source,
                    11 => $prefix . 'Strpos',
                ],
            ],
            'global calls within a namespace' => ['namespaced_calls', [7 => $source, 8 => $source]],
            'methods, declarations and other string functions' => ['other_uses', []],
            'function imports within namespace blocks' => [
                'imports',
                [
                    17 => $prefix . 'Strpos',
                    18 => $prefix . 'Strrpos',
                    19 => $prefix . 'Strlen',
                    35 => $prefix . 'Strlen',
                    39 => $prefix . 'Strlen',
                    45 => $prefix . 'Strlen',
                    49 => $prefix . 'Strlen',
                ],
            ],
            'function imports across namespace declarations' => [
                'semicolon_imports',
                [10 => $prefix . 'Strlen', 15 => $prefix . 'Strtoupper', 16 => $prefix . 'Strlen'],
            ],
            'suppress warnings by function' => [
                'suppression',
                [10 => '@Source: moodle.Strings.PreferCoreText.Strlen', 13 => $source],
            ],
        ];
    }

    /**
     * Test warnings across standards.
     *
     * @dataProvider standardsProvider
     * @param string $standard Coding standard.
     */
    public function testWarningsAreEnabledAndNotFixable(string $standard): void {
        $config = new Config([], false);
        $config->cache = false;
        $config->standards = [$standard];
        $config->sniffs = ['moodle.Strings.PreferCoreText'];
        $ruleset = new Ruleset($config);
        $path = __DIR__ . '/fixtures/calls.php';
        $file = new LocalFile($path, $ruleset, $config);
        $file->process();

        $this->assertSame([], $file->getErrors());
        $this->assertSame(10, $file->getWarningCount());
        $this->assertSame(0, $file->getFixableCount());
        $file->fixer->fixFile();
        $this->assertSame(file_get_contents($path), $file->fixer->getContents());
    }

    /**
     * Coding standards to test.
     *
     * @return array
     */
    public static function standardsProvider(): array {
        return [
            'Moodle 5.x' => ['moodle'],
            'Moodle 6.0' => ['moodle600'],
            'extended standard' => ['moodle-extra'],
        ];
    }
}
