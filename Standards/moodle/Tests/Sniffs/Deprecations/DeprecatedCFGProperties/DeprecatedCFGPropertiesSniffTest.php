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
    /**
     * @dataProvider provider
     */
    public function testSniff(
        string $fixtureName,
    ): void {
        $fixturePath = sprintf("%s/fixtures/%s.php", __DIR__, $fixtureName);
        $rulesetPath = sprintf("%s/fixtures/%s.xml", __DIR__, $fixtureName);

        $this->setStandard($rulesetPath);
        $this->setSniff('moodle.Deprecations.DeprecatedCFGProperties');
        [
            'errors'   => $errors,
            'warnings' => $warnings
        ] = $this->parseExpectationsFromFixture($fixturePath);

        $this->setFixture($fixturePath);
        $this->setWarnings($warnings);
        $this->setErrors($errors);

        $this->verifyCsResults();
    }

    public static function provider(): array {
        $iterator = new \DirectoryIterator(__DIR__ . '/fixtures');
        $fixtures = [];
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'php') {
                $fixtures[] = $fileinfo->getBasename('.php');
            }
        }

        return array_map(fn($fixture) => ['fixtureName' => $fixture], $fixtures);
    }
}
