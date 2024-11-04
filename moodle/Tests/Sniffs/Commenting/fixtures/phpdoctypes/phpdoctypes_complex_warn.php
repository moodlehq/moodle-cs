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

/**
 * A collection of valid types for testing
 *
 * This file should have no errors when checked with either PHPStan or Psalm.
 * Having just valid code in here means it can be easily checked with other checkers,
 * to verify we are actually checking against correct examples.
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Commenting\fixtures;

/**
 * A collection of valid types for testing
 *
 * @package   local_codechecker
 * @copyright 2023 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 * @template  T of ?int
 * @property ?int $p
 */
class php_valid {

    /**
     * @param ?int $p
     * @return ?int
     */
    function f(?int $p): ?int {
        return $p;
    }

    /** @var ?int */
    public ?int $v;

}

/** @var ?int */
$v2 = 0;
