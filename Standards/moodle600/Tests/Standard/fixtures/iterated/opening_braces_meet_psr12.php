<?php

/**
 * This fixture tests the use of PSR-12 Opening Function Braces.
 *
 * We test that the opening braces for functions and methods meet the PSR-12 standard.
 * Explicitly include the now-disabled sniff, Generic.Functions.OpeningFunctionBraceKernighanRitchie,
 * to ensure that it is no longer applied.
 */
// @sniff Generic.Functions.OpeningFunctionBraceKernighanRitchie
// @sniff PSR2.Classes.ClassDeclaration
// @sniff Squiz.Functions.MultiLineFunctionDeclaration

defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

// These were wrong in OpeningFunctionBraceKernighanRitchie, now it only fails because it lacks the new line.
// Functions.
// @warning Opening brace should be on a new line
function test01():int{
}

// @warning Opening brace should be on a new line
function test02():?int{
}

// @warning Opening brace should be on a new line
function test03():int   {
}

// @warning Opening brace should be on a new line
function test04():?int   {
}

// Methods.
// @warning Opening brace of a class must be on the line after the definition
class testbad {
    // @warning Opening brace should be on a new line
    public function test01():int{
    }

    // @warning Opening brace should be on a new line
    public function test02():?int{
    }

    // @warning Opening brace should be on a new line
    public function test03():int   {
    }

    // @warning Opening brace should be on a new line
    public function test04():?int   {
    }
}

// These were ok in in OpeningFunctionBraceKernighanRitchie, now they are wrong because of the lack of new line.
// All together with and without nullable question mark.
// Functions.
// @warning Opening brace should be on a new line
function test05():int {
}

// @warning Opening brace should be on a new line
function test06():?int {
}

// One space everywhere, with and without nullable question marks.
// @warning Opening brace should be on a new line
function test07() : int {
}

// @warning Opening brace should be on a new line
function test08() : ? int {
}

// Multiple spaces everywhere,  with and without nullable question marks.
// @warning Opening brace should be on a new line
function test09()   :   int {
}

// @warning Opening brace should be on a new line
function test10()   :   ?   int {
}

// @warning Opening brace should be on a new line
function test11()   :?   int {
}

// @warning Opening brace should be on a new line
function test12()   :   ?int {
}

// @warning Opening brace should be on a new line
function test13():      ?int {
}

// Methods.
// @warning Opening brace of a class must be on the line after the definition
class test_still_bad {
    // All together with and without nullable question mark.
    // @warning Opening brace should be on a new line
    public function test05():int {
    }

    // @warning Opening brace should be on a new line
    public function test06():?int {
    }

    // One space everywhere, with and without nullable question marks.
    // @warning Opening brace should be on a new line
    public function test07() : int {
    }

    // @warning Opening brace should be on a new line
    public function test08() : ? int {
    }

    // Multiple spaces everywhere,  with and without nullable question marks.
    // @warning Opening brace should be on a new line
    public function test09()   :   int {
    }

    // @warning Opening brace should be on a new line
    public function test10()   :   ?   int {
    }

    // @warning Opening brace should be on a new line
    public function test11()   :?   int {
    }

    // @warning Opening brace should be on a new line
    public function test12()   :   ?int {
    }

    // @warning Opening brace should be on a new line
    public function test13():      ?int {
    }
}

function test_good_01()
{
}

function test_good_02(): void
{
}

function test_good_03(
    string $param,
): void {
}

class good_class
{
    public function test1(): void
    {
    }

    private function test2(
        string $string,
    ): void {
    }
}

class good_class_implements implements something
{
}

class good_class_implements_newlines implements
    something,
    somethingelse
{
}
