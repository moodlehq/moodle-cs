<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

namespace Example\Thing;

use PHPUnit\Framework\Attributes\CoversNothing;

class correct_test extends base_test {
    #[CoversNothing]
    public function test_one() {
        // Nothing to test.
    }

    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function test_two() {
        // Nothing to test.
    }

    public function test_three() {
        // Nothing to test.
    }
}
