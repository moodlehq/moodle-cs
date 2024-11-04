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
 * A collection of types not in recommended style for testing
 *
 * These needn't give errors in PHPStan or Psalm.
 * But the PHPDocTypesSniff should give warnings.
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */

/**
 * A collection of types not in recommended style for testing
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 * @property Integer $p PHPDoc class property type doesn't conform to recommended style
 */
class types_invalid {

    /**
     * @param Boolean|T $p PHPDoc function parameter type doesn't conform to recommended style
     * @return Integer PHPDoc function return type doesn't conform to recommended style
     * @template T of Integer PHPDoc tempate type doesn't conform to recommended style
     */
    public function fun_wrong($p): int {
        return 0;
    }

    /** @var Integer PHPDoc var type doesn't conform to recommended style */
    public int $v1;

    /** @var Integer
     *      | Boolean Multiline type, no line break at end */
    public int|bool $v2;

    /** @var Integer
     *      | Boolean Multiline type, line break at end
     */
    public int|bool $v3;

}

/** @var Integer PHPDoc var type doesn't conform to recommended style (not class var) */
$v4 = 0;
