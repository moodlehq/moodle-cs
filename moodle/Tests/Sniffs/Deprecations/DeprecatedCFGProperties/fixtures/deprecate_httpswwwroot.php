<?php
defined('MOODLE_INTERNAL') || die(); // Make this always the 1st line in all CS fixtures.

function ok_usages() {
    global $CFG;

    $a = $CFG->wwwroot;
    $b = $CFG->dirroot;
    return $a . $b;
}

function bad_usages() {
    global $CFG;
    // @warning $CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead
    $a = $CFG->httpswwwroot;
    // @warning $CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead
    $b = $CFG -> httpswwwroot;
    // @warning $CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead
    $c = "The site is at $CFG->httpswwwroot right now.";
    $d = "Escaped \$CFG->httpswwwroot should not match.";
    return [$a, $b, $c, $d];
}

class other_cfg {
    public $httpswwwroot = 'not-global';
}

function unrelated_object() {
    $cfg = new other_cfg();
    return $cfg->httpswwwroot;
}
