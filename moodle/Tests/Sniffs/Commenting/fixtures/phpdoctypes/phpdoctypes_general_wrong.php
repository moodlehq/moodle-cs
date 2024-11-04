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
 * A collection of invalid types for testing
 *
 * Most type annotations give an error either when checked with PHPStan or Psalm.
 * Having just invalid types in here means the number of errors should match the number of type annotations.
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */

/**
 * PHPDoc misplaced tag
 * @property int $p
 */

/**
 * A collection of invalid types for testing
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 * @property int< PHPDoc class property type missing or malformed
 * @property int PHPDoc class property name missing or malformed
 */
class types_invalid {

    /**
     * Function parameter issues
     * @param int< PHPDoc function parameter type missing or malformed
     * @param int PHPDoc function parameter name missing or malformed
     * @param int $p1 PHPDoc function parameter doesn't exist
     * @param int $p2
     * @param int $p2 PHPDoc function parameter repeated
     * @param string $p3 PHPDoc function parameter type mismatch
     * @param int ...$p5 PHPDoc function parameter splat mismatch
     * @param int $p4 PHPDoc function parameter order wrong
     * @return void
     */
    public function function_parameter_issues(int $p2, int $p3, int $p4, int $p5): void {
    }

    /**
     * PHPDoc multiple function @return tags--Put in one tag, seperated by vertical bars |
     * @return int
     * @return null
     */
    function multiple_returns(): ?int {
        return 0;
    }

    /**
     * PHPDoc function return type missing or malformed
     * @return
     */
    function return_malformed(): void {
    }

    /**
     * PHPDoc function return type mismatch
     * @return string
     */
    function return_mismatch(): int {
        return 0;
    }

    /**
     * Template issues
     * @template @ PHPDoc template name missing or malformed
     * @template T of @ PHPDoc template type missing or malformed
     * @return void
     */
    function template_issues(): void {
    }

    /** @var @ PHPDoc var type missing or malformed */
    public int $var_type_malformed;
 
    /** @var string PHPDoc var type mismatch */
    public int $var_type_mismatch;

}

/** @var @ PHPDoc var type missing or malformed (not class var) */
$var_type_malformed_2 = 0;
