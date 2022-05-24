<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.
$context = context_system::instance();
// phpcs:set moodle.Access.DeprecatedCapability capabilitiesWarningList[] moodle/course:useremail
// phpcs:set moodle.Access.DeprecatedCapability capabilitiesErrorList[] moodle/site:useremail
has_capability('moodle/course:useremail', $context);
has_capability('moodle/site:useremail', $context);

