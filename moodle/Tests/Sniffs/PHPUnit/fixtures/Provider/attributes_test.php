<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

use PHPUnit\Framework\Attributes\DataProvider;

class correct_test extends base_test {
    public function test_one(): void {
        // Nothing to test.
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    #[DataProvider('provider_two')]
    #[DataProvider('provider_three')]
    #[\PHPUnit\Framework\Attributes\DataProviderExternal('provider_two')]
    public function test_two(): void {
        // Nothing to test.
    }

    public static function provider(): array {
        return [];
    }

    public static function provider_two(): Generator {
        yield [];
    }

    public static function provider_three(): \Generator {
        yield [];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provider', 'provider_two')]
    #[\PHPUnit\Framework\Attributes\DataProvider('missing_provider')]
    #[\PHPUnit\Framework\Attributes\DataProvider]
    public function test_with_parameters(): void {}

    #[\PHPUnit\Framework\Attributes\Test]
    #[\PHPUnit\Framework\Attributes\DataProvider('missing_provider')]
    public function this_is_a_test(): void {
        // Nothing to test.
    }
}
