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
 * A fixture to verify various phpdoc tags in a general location.
 *
 * @package   local_moodlecheck
 * @copyright 2018 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;

/**
 * A fixture to verify various phpdoc tags in a general location.
 *
 * @package   local_moodlecheck
 * @copyright 2018 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class fixturing_general {

    /**
     * Correct param types.
     *
     * @param string|bool $one
     * @param bool $two
     * @param array $three
     * @return void
     */
    public function correct_param_types($one, bool $two, array $three): void {
        echo "yay!";
    }

    /**
     * Correct param types.
     *
     * @param string|bool $one
     * @param bool $two
     * @param array $three
     * @return void
     */
    public function correct_param_types1($one, bool $two, array $three): void {
        echo "yay!";
    }

    /**
     * Correct param types.
     *
     * @param string $one
     * @param bool $two
     * @return void
     */
    public function correct_param_types2($one, $two): void {
        echo "yay!";
    }

    /**
     * Correct param types.
     *
     * @param string|null $one
     * @param bool $two
     * @param array $three
     * @return void
     */
    public function correct_param_types3(?string $one, bool $two, array $three): void {
        echo "yay!";
    }

    /**
     * Correct param types.
     *
     * @param string $one
     * @param bool $two
     * @param int[]|null $three
     * @return void
     */
    public function correct_param_types4($one, bool $two, array $three = null): void {
        echo "yay!";
    }

    /**
     * Correct param types.
     *
     * @param string $one
     * @param mixed ...$params one or more params
     * @return void
     */
    public function correct_param_types5(string $one, ...$params): void {
        echo "yay!";
    }

    /**
     * Correct return type.
     *
     * @return string
     */
    public function correct_return_type(): string {
        return "yay!";
    }

    /**
     * Namespaced types.
     *
     * @param \stdClass $data
     * @param \core\user $user
     * @return \core\user
     */
    public function namespaced_parameter_type(
        \stdClass $data,
        \core\user $user
    ): \core\user {
        return $user;
    }

    /**
     * Namespaced types.
     *
     * @param null|\stdClass   $data
     * @param null|\core\test\something|\core\some\other_thing $moredata
     * @return \stdClass
     */
    public function builtin(
        ?\stdClass $data,
        \core\test\something|\core\some\other_thing|null $moredata
    ): \stdClass {
        return new stdClass();
    }
}
