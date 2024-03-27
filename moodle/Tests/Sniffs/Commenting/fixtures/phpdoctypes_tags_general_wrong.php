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

defined('MOODLE_INTERNAL') || die();

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
     * Incomplete param annotation (type is missing).
     *
     * @param $one
     * @param $two
     */
    public function incomplete_param_annotation($one, $two) {
        echo "yoy!";
    }

    /**
     * Missing param definition.
     *
     * @param string $one
     * @param bool $two
     */
    public function missing_param_defintion() {
        echo "yoy!";
    }

    /**
     * Missing param annotation.
     */
    public function missing_param_annotation($one, $two) {
        echo "yoy!";
    }

    /**
     * Incomplete param definition.
     *
     * @param string $one
     * @param bool $two
     */
    public function incomplete_param_definition(string $one) {
        echo "yoy!";
    }

    /**
     * Incomplete param annotation (annotation is missing).
     *
     * @param string $one
     */
    public function incomplete_param_annotation1(string $one, $two) {
        echo "yoy!";
    }

    /**
     * Mismatch param types.
     *
     * @param string $one
     * @param bool $two
     */
    public function mismatch_param_types(string $one, array $two = []) {
        echo "yoy!";
    }

    /**
     * Mismatch param types.
     *
     * @param string|bool $one
     * @param bool $two
     */
    public function mismatch_param_types1(string $one, bool $two) {
        echo "yoy!";
    }

    /**
     * Mismatch param types.
     *
     * @param string|bool $one
     * @param bool $params
     */
    public function mismatch_param_types2(string $one, ...$params) {
        echo "yoy!";
    }

    /**
     * Mismatch param types.
     *
     * @param string $one
     * @param int[] $params
     */
    public function mismatch_param_types3(string $one, int $params) {
        echo "yoy!";
    }

    /**
     * Incomplete return annotation (type is missing).
     *
     * @return
     */
    public function incomplete_return_annotation() {
        echo "yoy!";
    }

}
