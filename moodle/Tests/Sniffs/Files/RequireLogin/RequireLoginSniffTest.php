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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Files\RequireLogin;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Test the RequireLoginSniff sniff.
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Files\RequireLoginSniff
 */
class RequireLoginSniffTest extends MoodleCSBaseTestCase
{
    /**
     * @dataProvider qualifiedCallsProvider
     */
    public function testQualifiedCalls(string $fixture, array $warnings): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.RequireLogin');
        $this->setFixture(__DIR__ . '/../fixtures/RequireLogin/' . $fixture . '.php');
        $this->setErrors([]);
        $this->setWarnings($warnings);
        $this->verifyCsResults();
    }

    public static function qualifiedCallsProvider(): array {
        return [
            'global login' => ['qualified_login', []],
            'global CLI definition' => ['qualified_define', []],
            'namespaced login is not global' => ['namespaced_login', [2 => 'None found.']],
        ];
    }

    public function testNoConfigInclusionDoesNotRequireLogin(): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.RequireLogin');
        $this->setFixture(__DIR__ . '/../fixtures/RequireLogin/no_config.php');

        $this->setErrors([]);
        $this->setWarnings([]);

        $this->verifyCsResults();
    }

    public function testNonConfigIncludesBeforeConfigDoNotSkipLoginCheck(): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.RequireLogin');
        $this->setFixture(__DIR__ . '/../fixtures/RequireLogin/other_include.php');

        $this->setErrors([]);
        $this->setWarnings([
            4 => 'None found.',
        ]);

        $this->verifyCsResults();
    }

    public function testAbortAfterConfigDisablesLoginCheck(): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.Files.RequireLogin');
        $this->setFixture(__DIR__ . '/../fixtures/RequireLogin/abortafterconfig.php');

        $this->setErrors([]);
        $this->setWarnings([]);

        $this->verifyCsResults();
    }
}
