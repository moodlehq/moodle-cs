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

/**
 * Visit {@link https://moodle.org} or {@link https://example.com More information}.
 * {@see has_capability} and {@see has_capability() Permissions} are valid.
 * See {@see \core\example::method()} and {@see example::$property}.
 * Numeric characters in structural references are allowed: {@see example6::method()}.
 * Also {@see \so-me\com-plex\th_ing::$come->ba8by()} is supported for compatibility.
 * See {@see $this->property} and {@see example::CONSTANT}.
 * Contact someone@example.com rather than another@see.example.
 *
 * @see https://moodle.org
 * @link https://moodle.org
 * @param int $value See {@see example::method()} for details.
 * @return string Visit {@link https://moodle.org}.
 * Read {@link https://example.com/@someone Profile for @someone}.
 * Contact someone-@example.com or browse https://example.com/@see.
 * Adjacent{@see example()} references are also valid.
 * Unknown {@unknown anything} and @someone are ignored.
 * Also {@see ::setSniffs()}, {@see ::CONSTANT}, and {@see ::$property}.
 * Read {@link https://moodle.org
 * with a description on another line}.
 * {@see ::setSniffs()
 * and its description}.
 * {@link https://moodle.org
 * @someone in the description}.
 * {@link https://moodle.org
 * @see another inline tag in the description} followed by {@see ::method()}.
 * See {@see example::method($value)}.
 */
// Ordinary comments can contain {@anything}.
/* @param standalone tags are not inline in block comments. */
/*
 * @param standalone tags are not inline here either.
 * A reference to {@see example::method($value)}.
 */
// A valid {@link https://moodle.org} and {@unknown anything}.
/// @param int $value is a standalone tag.
/// A valid {@see example::method()}.
$literal = '{@param int $value}';
