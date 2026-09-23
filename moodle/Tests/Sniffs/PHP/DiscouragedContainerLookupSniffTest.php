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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\PHP;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Tests for discouraged container lookups inside classes.
 *
 * @copyright 2026 Cameron Ball <cameron@cameron1729.xyz>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\MoodleExtra\Sniffs\PHP\DiscouragedContainerLookupSniff
 */
class DiscouragedContainerLookupSniffTest extends MoodleCSBaseTestCase
{
    /**
     * Test lookups using different namespace and alias forms.
     */
    public function testDiscouragedContainerLookups(): void {
        $this->setStandard('moodle-extra');
        $this->setSniff('MoodleExtra.PHP.DiscouragedContainerLookup');
        $this->setFixture(
            __DIR__ . '/fixtures/DiscouragedContainerLookup/general.php',
            '/var/www/local/example/classes/service.php'
        );

        $this->setErrors([]);
        $this->setWarnings([
            40 => 'Calling core\di::get() inside a class is service location',
            41 => 'Calling core\di::get() inside a class is service location',
            42 => 'Calling core\di::get() inside a class is service location',
            43 => 'Calling core\di::get_container() inside a class is service location',
            49 => 'Calling core\di::get() inside a class is service location',
            55 => 'Calling core\di::get() inside a class is service location',
            68 => 'Calling core\di::get() inside a class is service location',
            76 => 'Calling core\di::get() inside a class is service location',
            77 => 'Calling core\di::get_container() inside a class is service location',
        ]);

        $this->verifyCsResults();
    }

    /**
     * Test known container infrastructure.
     */
    public function testAllowedContainerLookups(): void {
        $this->setStandard('moodle-extra');
        $this->setSniff('MoodleExtra.PHP.DiscouragedContainerLookup');
        $this->setFixture(
            __DIR__ . '/fixtures/DiscouragedContainerLookup/allowed.php',
            '/var/www/lib/classes/router/bridge.php'
        );
        $this->setErrors([]);
        $this->setWarnings([]);

        $this->verifyCsResults();
    }

    /**
     * Test that test paths are ignored by default.
     */
    public function testTestFilesIgnored(): void {
        $this->setStandard('moodle-extra');
        $this->setSniff('MoodleExtra.PHP.DiscouragedContainerLookup');
        $this->setFixture(
            __DIR__ . '/fixtures/DiscouragedContainerLookup/test_file.php',
            '/var/www/local/example/tests/service_test.php'
        );
        $this->setErrors([]);
        $this->setWarnings([]);

        $this->verifyCsResults();
    }
}
