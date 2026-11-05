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

// @error moodle.Commenting.FunctionArguments.ParamCount
/** Missing parameter documentation. */
function missing(string $value): void {
}

// @error moodle.Commenting.FunctionArguments.ParamCount
/**
 * @param string $value
 */
function extra(): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidParam
 * @param $value
 */
function missing_type($value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidParam
 * @param type $value
 */
function placeholder($value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamName
 * @param string $second
 * // @error moodle.Commenting.FunctionArguments.ParamName
 * @param string $first
 */
function wrong_order(string $first, string $second): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param string|bool $value
 */
function wrong_union(string $value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param int[] $value
 */
function wrong_array(int $value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param string $value
 */
function missing_null(?string $value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidParam
 * @param
 * // @error moodle.Commenting.FunctionArguments.InvalidReturn
 * @return
 */
function empty_tags($value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidReturn
 * @return type
 */
function placeholder_return() {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidParam
 * @param string
 */
function missing_name(string $value): void {
}

/**
 * @param string $value
 * // @error moodle.Commenting.FunctionArguments.InvalidReturn
 * @return
 */
function empty_return(string $value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamName
 * @param string $wrong
 */
function untyped_wrong_name($value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param integer $number
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param boolean $flag
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param double $decimal
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param STRING $text
 * // @error moodle.Commenting.FunctionArguments.ParamType
 * @param \Vendor\example $object
 */
function aliases_and_case(int $number, bool $flag, float $decimal, string $text, \Vendor\Example $object): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidParam
 * @param
 * @return string
 */
function empty_param_before_tag($value): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidReturn
 * @return
 * @see another_function
 */
function empty_return_before_tag(): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.InvalidReturn
 * @return
 *
 * A separate paragraph is not a return type.
 */
function empty_return_before_paragraph(): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamName
 * @param string $values
 */
function missing_variadic_marker(string ...$values): void {
}

/**
 * // @error moodle.Commenting.FunctionArguments.ParamName
 * @param string ...$value
 */
function unexpected_variadic_marker(string $value): void {
}
