<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

namespace Example\Thing;

use PHPUnit\Framework\Attributes as PHPUnitAttributes;

#[PHPUnitAttributes\CoversClass(\some\namespace\one::class)]
class correct_test extends base_test {
    public function test_one() {
        // Nothing to test.
    }
}

