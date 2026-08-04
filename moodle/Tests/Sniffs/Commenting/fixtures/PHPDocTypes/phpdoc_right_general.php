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
 * And no errors when checked with the PHPDoc types sniff.
 *
 * @copyright 2023-2026 James Calder and Otago Polytechnic
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * A collection of valid types for testing
 */
class TypesValid {
    /**
     * Basic type equivalence
     *
     * @param array      $array
     * @param bool       $bool
     * @param int        $int
     * @param float      $float
     * @param string     $string
     * @param object     $object
     * @param self       $self
     * @param iterable   $iterable
     * @param TypesValid $specificclass
     * @param callable   $callable
     *
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
        TypesValid $specificclass,
        callable $callable
    ): void {
    }

    /**
     * Types not supported natively (as of PHP 7.2)
     *
     * @param resource $resource
     * @param static   $static
     * @param mixed    $mixed
     *
     * @return never
     */
    public function nonnative_types($resource, $static, $mixed) {
        throw new \Exception();
    }

    /**
     * Parameter modifiers
     *
     * @param object &$reference
     * @param int    ...$splat
     *
     * @return void
     */
    public function parameter_modifiers(
        object &$reference,
        int ...$splat
    ): void {
    }

    /**
     * Boolean types
     *
     * @param bool       $bool
     * @param true|false $literal
     *
     * @return void
     */
    public function boolean_types(bool $bool, bool $literal): void {
    }

    /**
     * Object types
     *
     * @param object            $object
     * @param TypesValid        $class
     * @param self|static|$this $relative
     * @param Traversable       $traversable
     * @param \Closure          $closure
     *
     * @return void
     */
    public function object_types(
        object $object,
        object $class,
        object $relative,
        object $traversable,
        object $closure
    ): void {
    }

    /**
     * Null type
     *
     * @param null     $standalonenull
     * @param int|null $explicitnullable
     *
     * @return void
     */
    public function null_type(
        $standalonenull,
        ?int $explicitnullable
    ): void {
    }

    /**
     * User-defined type
     *
     * @param TypesValid|\TypesValid $class
     *
     * @return void
     */
    public function user_defined_type(TypesValid $class): void {
    }

    /**
     * Iterable types
     *
     * @param array    $array
     * @param iterable $iterable
     *
     * @return void
     */
    public function iterable_types(iterable $array, iterable $iterable): void {
    }

    /**
     * Basic structure
     *
     * @param int|string        $union
     * @param TypesValid&object $intersection
     * @param int[]             $arraysuffix
     *
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
     *
     * @param int|float|string               $multipleunion
     * @param TypesValid&object&\Traversable $multipleintersection
     * @param int[][]                        $multiplearray
     * @param int|int[]                      $unionarray
     * @param (int)[]                        $bracketarray
     * @param int|TypesValid&object          $dnf
     *
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
     * Post PHP 7.2 features
     *
     * @param a|c   $dropunion
     * @param a&b&c $addintersection
     * @param a&c   $both
     * @param a|b&c $dnf
     *
     * @return $this
     */
    public function post_php_72(
        a|b|c $dropunion,
        a&c $addintersection,
        a|b $both,
        a|(b&c) $dnf
    ): static {
        return $this;
    }
}

/**
 * Interface for type checks
 */
interface a {
}

/**
 * Interface for type checks
 */
interface b {
}

/**
 * Interface for type checks
 */
interface c {
}
