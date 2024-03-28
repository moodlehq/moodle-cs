<?php

class Example
{
    /**
     * This is an example of a very long string within the docblock of a class.
     *
     * Checks that all actiities in the specified section are hidden. You need to be in the course page. It can be used being logged as a student and as a teacher on editing mode.
     *
     * @Given /^I change the name of the "(?P<activity_name_string>(?:[^"]|\\")*)" activity name to "(?P<new_name_string>(?:[^"]|\\")*)"$/
     */
    public function i_change_names(): void {
        // This is also a really stupidly long comment but this one is not allowed to be over long. The reason we accept long docblock strings but not long comments string is because
        // docblocks are used as code.
    }
}
