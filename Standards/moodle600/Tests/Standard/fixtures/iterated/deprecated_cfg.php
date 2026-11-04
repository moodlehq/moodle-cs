<?php

/**
 * This fixture tests the deprecation of CFG values.
 *
 * The following deprecated CFG values are tested:
 * - $CFG->httpswwwroot
 * - $CFG->admin
 * - $CFG->loginhttps
 *
 * The following CFG values are not deprecated:
 * - $CFG->wwwroot
 * - $CFG->dirroot
 */
// @sniff moodle.Deprecations.DeprecatedCFGProperties

function ok_usages() {
    global $CFG;

    $a = $CFG->wwwroot;
    $b = $CFG->dirroot;
    return $a . $b;
}

function bad_usages() {
    global $CFG;

    $data = [];
    // @warning $CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead
    $data[] = $CFG->httpswwwroot;
    // @warning $CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead
    $data[] = $CFG -> httpswwwroot;
    // @warning $CFG->httpswwwroot is deprecated; use $CFG->wwwroot instead
    $data[] = "The site is at $CFG->httpswwwroot right now.";
    $data[] = "Escaped \$CFG->httpswwwroot should not match.";

    // @warning $CFG->admin is deprecated and should not be used
    $data[] = $CFG->admin;
    // @warning $CFG->admin is deprecated and should not be used
    $data[] = $CFG->admin;
    // @warning $CFG->admin is deprecated and should not be used
    $data[] = "The value is $CFG->admin right now.";
    $data[] = "Escaped \$CFG->admin should not match.";

    // @warning $CFG->loginhttps is deprecated and should not be used
    $data[] = $CFG->loginhttps;

    return $data;
}

class other_cfg {
    public $httpswwwroot = 'not-global';
    public $admin = 'not-global';
}

function unrelated_object() {
    $cfg = new other_cfg();

    return [
        $cfg->httpswwwroot,
        $cfg->admin,
        $cfg->loginhttps,
    ];
}
