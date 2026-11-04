<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

function ok_usages() {
    global $CFG;
    $a = $CFG->adminthing;
    $b = $CFG->thingadmin;
    return $a . $b;
}

function bad_usages() {
    global $CFG;
    // @warning $CFG->admin is deprecated and should not be used
    $a = $CFG->admin;
    // @warning $CFG->admin is deprecated and should not be used
    $b = $CFG -> admin;
    // @warning $CFG->admin is deprecated and should not be used
    $c = "The value is $CFG->admin right now.";
    $d = "Escaped \$CFG->admin should not match.";
    return [$a, $b, $c, $d];
}

class other_cfg {
    public $admin = 'not-global';
}

function unrelated_object() {
    $cfg = new other_cfg();
    return $cfg->admin;
}
