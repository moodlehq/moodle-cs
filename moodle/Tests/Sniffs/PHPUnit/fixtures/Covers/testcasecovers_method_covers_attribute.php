<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

namespace Example\Thing;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\CoversTrait;

class correct_test extends base_test {
    #[CoversClass(\some\namespace\one::class)]
    public function test_one() {
        // Nothing to test.
    }

    #[CoversTrait(\some\namespace\two::class)]
    public function test_two() {
        // Nothing to test.
    }

    #[CoversMethod(\some\namespace\three::class, 'someMethod')]
    public function test_three() {
        // Nothing to test.
    }

    #[CoversFunction('some_function')]
    public function test_four() {
        // Nothing to test.
    }
}

