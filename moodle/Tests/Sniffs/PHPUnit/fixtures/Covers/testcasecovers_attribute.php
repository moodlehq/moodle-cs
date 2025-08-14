<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

namespace Example\Thing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;

// A class with 3 methods, using all the covers options correctly.

#[CoversClass(\some\namespace\one::class)]
#[CoversFunction(\some\namespace\two::class)]
#[CoversClass(\some\namespace\three::class)]
class correct_test extends base_test {
    public function test_one() {
        // Nothing to test.
    }

    public function test_two() {
        // Nothing to test.
    }

    public function test_three() {
        // Nothing to test.
    }
}
