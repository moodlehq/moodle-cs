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
 * This file should have no errors when checked with either PHPStan or Psalm (but a warning about an unused var).
 * Having just valid code in here means it can be easily checked with other checkers,
 * to verify we are actually checking against correct examples.
 *
 * @package   local_codechecker
 * @copyright 2024 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Commenting\fixtures;

use stdClass as myStdClass, Exception;
use MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Commenting\ {PHPDocTypesSniffTest};

?>
<?php

/**
 * A parent class
 */
class php_valid_parent {
}

/**
 * An interface
 */
interface php_valid_interface {
}

/** A trait */
trait php_valid_trait {
}

/**
 * A collection of valid types for testing
 *
 * @package   local_codechecker
 * @copyright 2023 Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 * @template  T of int
 */
class php_valid extends php_valid_parent implements php_valid_interface {
    use php_valid_trait;

    /**
     * Namespaces recognised
     * @param \MoodleHQ\MoodleCS\moodle\Tests\Sniffs\Commenting\fixtures\php_valid<int> $x
     * @return void
     */
    function namespaces(php_valid $x): void {
    }

    /**
     * Uses recognised
     * @param \stdClass $x
     * @return void
     */
    function uses(myStdClass $x): void {
    }

    /**
     * Parents recognised
     * @param php_valid<int> $x
     * @return void
     */
    function parents(php_valid_parent $x): void {
    }

    /**
     * Interfaces recognised
     * @param php_valid<int> $x
     * @return void
     */
    function interfaces(php_valid_interface $x): void {
    }

    /**
     * Class templates recognised
     * @param T $x
     * @return void
     */
    function class_templates(int $x): void {
    }

    /**
     * Function templates recognised
     * @template T2 of int
     * @param T2 $x
     * @return void
     */
    function function_templates(int $x): void {
    }

    /**
     * Visibility accepted
     * @param int $x
     * @return void
     */
    public function visibility(int $x): void {
    }

    /**
     * Multiline comment
     * @param object{
     *   a: int,
     *   b: string
     * } $x
     * @return void
     */
    function multiline_comment(object $x): void {
    }
}

// Ignore things that don't concern us.
$x = 0;
