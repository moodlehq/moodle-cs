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
 * This file should have no errors when checked with either PHPStan or Psalm, other than no value for iterable.
 * Having just valid code in here means it can be easily checked with other checkers,
 * to verify we are actually checking against correct examples.
 *
 * @package   local_codechecker
 * @copyright 2023 onwards Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */

use stdClass as MyStdClass;

/**
 * A parent class
 */
class types_valid_parent {
}

/**
 * An interface
 */
interface types_valid_interface {
}

/**
 * A collection of valid types for testing
 *
 * @package   local_codechecker
 * @copyright 2023 onwards Otago Polytechnic
 * @author    James Calder
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later, CC BY-SA v4 or later, and BSD-3-Clause
 */
class types_valid extends types_valid_parent implements types_valid_interface {

    /**
     * Basic type equivalence
     * @param array $array
     * @param bool $bool
     * @param int $int
     * @param float $float
     * @param string $string
     * @param object $object
     * @param self $self
     * @param iterable $iterable
     * @param types_valid $specificclass
     * @param callable $callable
     * @return void
     */
    public function basic_type_equivalence(
        array $array,
        bool $bool,
        int $int,
        float $float,
        string $string,
        object $object,
        self $self,
        iterable $iterable,
        types_valid $specificclass,
        callable $callable
    ): void {
    }

    /**
     * Types not supported natively (as of PHP 7.2)
     * @param resource $resource
     * @param static $static
     * @param mixed $mixed
     * @return never
     */
    public function non_native_types($resource, $static, $mixed) {
        throw new \Exception();
    }

    /**
     * Parameter modifiers
     * @param object &$reference
     * @param int ...$splat
     * @return void
     */
    public function parameter_modifiers(
        object &$reference,
        int ...$splat): void {
    }

    /**
     * Boolean types
     * @param bool $bool
     * @param true|false $literal
     * @return void
     */
    public function boolean_types(bool $bool, bool $literal): void {
    }


    /**
     * Object types
     * @param object $object
     * @param types_valid $class
     * @param self|static|$this $relative
     * @param Traversable $traversable
     * @param \Closure $closure
     * @return void
     */
    public function object_types(object $object, object $class,
        object $relative, object $traversable, object $closure): void {
    }

    /**
     * Null type
     * @param null $standalonenull
     * @param int|null $explicitnullable
     * @param int|null $implicitnullable
     * @return void
     */
    public function null_type(
        $standalonenull,
        ?int $explicitnullable,
        int $implicitnullable=null
    ): void {
    }

    /**
     * User-defined type
     * @param types_valid|\types_valid $class
     * @return void
     */
    public function user_defined_type(types_valid $class): void {
    }

    /**
     * Callable types
     * @param callable $callable
     * @param \Closure $closure
     * @return void
     */
    public function callable_types(callable $callable, callable $closure): void {
    }

    /**
     * Iterable types
     * @param array $array
     * @param iterable $iterable
     * @param Traversable $traversable
     * @return void
     */
    public function iterable_types(iterable $array, iterable $iterable, iterable $traversable): void {
    }

    /**
     * Basic structure
     * @param int|string $union
     * @param types_valid&object $intersection
     * @param int[] $arraysuffix
     * @return void
     */
    public function basic_structure(
        $union,
        object $intersection,
        array $arraysuffix
    ): void {
    }

    /**
     * Structure combinations
     * @param int|float|string $multipleunion
     * @param types_valid&object&\Traversable $multipleintersection
     * @param int[][] $multiplearray
     * @param int|int[] $unionarray
     * @param (int)[] $bracketarray
     * @param int|(types_valid&object) $dnf
     * @return void
     */
    public function structure_combos(
        $multipleunion,
        object $multipleintersection,
        array $multiplearray,
        $unionarray,
        array $bracketarray,
        $dnf
    ): void {
    }

    /**
     * DocType DNF vs Native DNF
     * @param int|(types_valid_parent&types_valid_interface) $p
     */
    function dnf_vs_dnf((types_valid_interface&types_valid_parent)|int $p): void {
    }

    /**
     * Inheritance
     * @param types_valid $basic
     * @param self|static|$this $relative1
     * @param types_valid $relative2
     * @return void
     */
    public function inheritance(
        types_valid_parent $basic,
        parent $relative1,
        parent $relative2
    ): void {
    }

    /**
     * Template
     * @template T of int
     * @param T $template
     * @return void
     */
    public function template(int $template): void {
    }

    /**
     * Use alias
     * @param stdClass $use
     * @return void
     */
    public function uses(MyStdClass $use): void {
    }

    /**
     * Built-in classes with inheritance
     * @param Traversable|Iterator|Generator|IteratorAggregate $traversable
     * @param Iterator|Generator $iterator
     * @param Throwable|Exception|Error $throwable
     * @param Exception|ErrorException $exception
     * @param Error|ArithmeticError|AssertionError|ParseError|TypeError $error
     * @param ArithmeticError|DivisionByZeroError $arithmeticerror
     * @return void
     */
    public function builtin_classes(
        Traversable $traversable, Iterator $iterator,
        Throwable $throwable, Exception $exception, Error $error,
        ArithmeticError $arithmeticerror
    ): void {
    }

    /**
     * SPL classes with inheritance (a few examples only)
     * @param Iterator|SeekableIterator|ArrayIterator $iterator
     * @param SeekableIterator|ArrayIterator $seekableiterator
     * @param Countable|ArrayIterator $countable
     * @return void
     */
    public function spl_classes(
        Iterator $iterator, SeekableIterator $seekableiterator, Countable $countable
    ): void {
    }

}
