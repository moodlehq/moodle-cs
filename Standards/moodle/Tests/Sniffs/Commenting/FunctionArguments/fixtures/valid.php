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

use core\example;

function undocumented(string $value): void {
}

/** No arguments or return tag are required here. */
function no_arguments(): void {
}

/**
 * @param int|string $value
 * @param string[]|string $items
 * @param \stdClass|null $object
 * @param bool $flag
 * @return string|int
 */
function unions_and_arrays(
    string|int $value,
    string|array $items,
    ?stdClass $object,
    bool $flag = false,
): int|string {
    return $value;
}

/**
 * @param int $number
 * @param bool $flag
 * @param float $decimal
 * @param string|null $optional
 * @param mixed $untyped
 * @param int &$reference
 * @param string ...$values
 */
function aliases_and_modifiers(
    int $number,
    bool $flag,
    float $decimal,
    string $optional,
    $untyped,
    int &$reference,
    string ...$values,
): void {
}

class example {
    /**
     * @param string $name
     * @param int[] $values
     */
    #[Example]
    public function __construct(
        #[Example]
        public readonly string $name,
        private array $values = [],
    ) {
    }

    /**
     * @param \some\namespace\example $value
     */
    public function namespaced(\some\namespace\example $value): void {
    }
}

interface documented {
    /**
     * @param \Countable&\Iterator $value
     */
    public function intersection(\Iterator&\Countable $value): void;

    /**
     * @param null|(\Iterator&\Countable) $value
     */
    public function dnf((\Countable&\Iterator)|null $value): void;
}

/**
 * @param string $value
 */
function multiline(string $value)
: void {
}

/**
 * @return void
 */
function documented_return(): void {
}

// Anonymous functions do not need named-function PHPDoc.
$callback = function ($value) {
    return $value;
};
$arrow = fn($value) => $value;

/**
 * @param \core\example $value
 * @return example
 */
function imported_parameters(example $value): example {
}

/**
 * @param \Vendor\Type $value
 * @return \Vendor\Type
 */
function namespaced_type(\Vendor\Type $value): \Vendor\Type {
    return $value;
}

/**
 * @param string
 *     $value A description.
 * @return
 *     string A description.
 */
function continued_tags(string $value): string {
    return $value;
}

/**
 * @param string
 *     $value
 *     A description on a second continuation line.
 * @return string
 *     A description.
 */
function continued_description(string $value): string {
    return $value;
}

/**
 * @param string &...$values
 */
function variadic_reference(string &...$values): void {
}
