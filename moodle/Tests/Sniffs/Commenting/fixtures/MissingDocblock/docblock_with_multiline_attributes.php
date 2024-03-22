<?php

/**
 * This file contains multiple testcases for multi line attributes.
 */

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\PHPUnit;

defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

/**
 * Example class. 
 */
#[\Attribute(
    attr1: 'asdf',
    attr2: 'asdf',
)]
class class_multiline_attribute {

    /**
     * Method attribute.
     */
    #[\Attribute(
        attr1: 'asdf',
        attr2: 'asdf',
    )]
    function method_multiline_attribute(): void {
    }
}

/**
 * Interface with multiline attributes.
 */
#[\Attribute(
    attr1: 'asdf',
    attr2: 'asdf',
)]
interface interface_multiline_attribute {
}

/**
 * Trait with multiline attributes.
 */

#[\Attribute(
    attr1: 'asdf',
    attr2: 'asdf',
)]
trait trait_multiline_attribute {
}
