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

namespace MoodleHQ\MoodleCS\moodle600\Tests\Standard;

/**
 * Test configuration of the phpcs standard.
 *
 * @copyright  Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class StandardTest extends \MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase
{
    private const FIXTURE_PATH = __DIR__ . '/fixtures/iterated';

    /**
     * @dataProvider provider
     */
    public function testFixture(
        string $fixtureName,
    ): void {
        $fixturePath = sprintf("%s/%s.php", self::FIXTURE_PATH, $fixtureName);

        $this->setStandard('moodle600');
        [
            'errors'   => $errors,
            'warnings' => $warnings,
            'sniffs'   => $sniffs,
        ] = $this->parseExpectationsFromFixture($fixturePath);

        $this->setSniffs($sniffs);
        $this->setFixture($fixturePath);
        $this->setWarnings($warnings);
        $this->setErrors($errors);

        $this->verifyCsResults();
    }

    public static function provider(): array {
        $iterator = new \DirectoryIterator(self::FIXTURE_PATH);
        $fixtures = [];
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'php') {
                $fixtures[] = $fileinfo->getBasename('.php');
            }
        }

        return array_map(fn($fixture) => ['fixtureName' => $fixture], $fixtures);
    }
}
