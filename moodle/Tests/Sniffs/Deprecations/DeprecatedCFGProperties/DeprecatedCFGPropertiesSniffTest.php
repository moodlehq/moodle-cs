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

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Deprecations\DeprecatedCFGProperties;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;

/**
 * Test the DeprecatedCFGProperties sniff.
 *
 * @copyright  2026 Paul Holden <paulh@moodle.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\Deprecations\DeprecatedCFGPropertiesSniff
 */
class DeprecatedCFGPropertiesSniffTest extends MoodleCSBaseTestCase
{
    public function testMoodlePHPDeprecatedCFGProperties() {
        // Define the standard, sniff and fixture to use.
        $this->setStandard('moodle');
        $this->setSniff('moodle.Deprecations.DeprecatedCFGProperties');
        $this->setFixture(__DIR__ . '/../fixtures/DeprecatedCFGProperties/moodle_php_deprecated_cfg_properties.php');

        // Define expected results (errors and warnings). Format, array of:
        // - line => number of problems,  or
        // - line => array of contents for message / source problem matching.
        // - line => string of contents for message / source problem matching (only 1).
        $this->setErrors([]);
        $this->setWarnings([
            13 => '$CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead',
            14 => '$CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead',
            15 => '$CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead',
            16 => 0,
            27 => 0,
        ]);

        // Let's do all the hard work!
        $this->verifyCsResults();
    }
}
