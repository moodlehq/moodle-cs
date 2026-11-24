<?php
// Ordinary comment.
// @error moodle.Commenting.InlineComment.WrongStyle
/// Triple slash.
// @error moodle.Commenting.InlineComment.WrongStyle
//// Four slashes.
// @error moodle.Commenting.InlineComment.WrongStyle
# Hash comment.
if (true) {
// @error moodle.Commenting.InlineComment.WrongStyle
} /// After closing brace.
/*
/// Block continuation.
*/
// @error moodle.Commenting.InlineComment.WrongStyle
$value = 1; /// Trailing comment.
// @error moodle.Commenting.InlineComment.WrongStyle
/// Standalone.
// @error moodle.Commenting.InlineComment.WrongStyle
    /// Indented.
$fn = function() {
// @error moodle.Commenting.InlineComment.WrongStyle
}; /// After semicolon.
$list = [
    function() {
    // @error moodle.Commenting.InlineComment.WrongStyle
    }, /// After comma.
];
// @error moodle.Commenting.InlineComment.WrongStyle
/// Invalid {@param int $value}.
/*
 * Invalid {@throws Exception}.
 */
