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
 * Tests for the forbidden container injection sniff.
 *
 * @copyright 2026 Cameron Ball <cameron@cameron1729.xyz>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Sniffs\PHP\ForbiddenContainerInjectionSniff
 */
class ForbiddenContainerInjectionSniffTest extends MoodleCSBaseTestCase
{
    /**
     * Test forbidden types in different namespace and parameter type forms.
     */
    public function testForbiddenContainerInjection(): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.PHP.ForbiddenContainerInjection');
        $this->setFixture(__DIR__ . '/fixtures/ForbiddenContainerInjection/general.php');

        $this->setErrors([
            32 => 'The container type Psr\Container\ContainerInterface must not be injected',
            37 => 'The container type Psr\Container\ContainerInterface must not be injected',
            42 => 'The container type Psr\Container\ContainerInterface must not be injected',
            47 => 'The container type Psr\Container\ContainerInterface must not be injected',
            52 => 'The container type Psr\Container\ContainerInterface must not be injected',
            57 => 'The container type core\di must not be injected',
            62 => 'The container type DI\Container must not be injected',
            66 => 'The container type Psr\Container\ContainerInterface must not be injected',
            69 => 'The container type Psr\Container\ContainerInterface must not be injected',
            72 => 'The container type DI\Container must not be injected',
            77 => 'The container type Psr\Container\ContainerInterface must not be injected',
            84 => 'The container type core\di must not be injected',
            98 => 'The container type Psr\Container\ContainerInterface must not be injected',
            105 => 'The container type Psr\Container\ContainerInterface must not be injected',
        ]);
        $this->setWarnings([]);

        $this->verifyCsResults();
    }

    /**
     * Test the narrow allowlist for Moodle's container integration itself.
     */
    public function testContainerInfrastructureAllowlist(): void {
        $this->setStandard('moodle');
        $this->setSniff('moodle.PHP.ForbiddenContainerInjection');
        $this->setFixture(__DIR__ . '/fixtures/ForbiddenContainerInjection/allowed.php');
        $this->setErrors([]);
        $this->setWarnings([]);

        $this->verifyCsResults();
    }
}
