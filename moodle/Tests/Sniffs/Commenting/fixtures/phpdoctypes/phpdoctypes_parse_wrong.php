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
 * A collection of parse errors for testing
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */

namespace trailing_backslash\;

namespace @ // Malformed.

use no_trailing_backslash {something};

use trailing_backslash\;

use x\ { ; // No bracket closer.

use x\ {}; // No content.

use x as @; // Malformed as clause.

use x @ // No terminator.

/** @var int */
public int $wrong_place_1;

/** */
function wrong_places(): void {
    namespace ns;
    use x;
    /** */
    class c {}
    /** @var int */
    public int $wrong_place_2;
}

/**
 * A collection of parse errors for testing
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */
class types_invalid // No block

/** */
class c { // No block close

/** */
class c {
    use T { @
}

/** */
function f: void {} // No parameters

/** */
function f( : void {} // No parameters close

/** */
function f(): void // No block

/** */
function f(): void { // No block close

/** */
public @ // Malformed declaration.

/** @var int */
public int $v @ // Unterminated variable.

/** @param string $p */
function f(int $p): void {};  // Do we still reach here, and detect an error?

/** Unclosed Doc comment
