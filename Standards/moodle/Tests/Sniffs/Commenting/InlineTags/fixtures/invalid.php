<?php

// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

// @error moodle.Commenting.InlineTags.InvalidTag
/** Inline {@param int $value} is invalid. */
// @error moodle.Commenting.InlineTags.InvalidTag
/** Inline {@throws \Exception} is invalid. */
// @error moodle.Commenting.InlineTags.MissingBraces
/** Missing @see braces. */
// @error moodle.Commenting.InlineTags.MissingBraces
/** Missing @link https://moodle.org braces. */
// @error moodle.Commenting.InlineTags.MissingBraces
/** Missing {@see example() closing brace. */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@link not-a-url} */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@see https://moodle.org} */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@see $this->tagrules['url']} */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@see example::method($a,$b)} */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@see} */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@link } */
// @error 2
/** Duplicate {@see https://moodle.org} and {@see https://moodle.org}. */
// @error moodle.Commenting.InlineTags.MissingBraces
/** Valid {@see example()} followed by invalid @see example(). */
// @error moodle.Commenting.InlineTags.InvalidTag
/** @param int $value Inline {@return string} is invalid here too. */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@link not-a-url
 * continued description}.
 */
// @error moodle.Commenting.InlineTags.MissingBraces
/** {@link https://moodle.org
 * unfinished description.
 */
// @error moodle.Commenting.InlineTags.MissingBraces
/** {@see
 * @param string $value}
 */
// @error moodle.Commenting.InlineTags.MissingBraces
/** {@link
 * @return string}
 */
// @error moodle.Commenting.InlineTags.InvalidContents
/** {@see}
 * @param string $value
 */
// @error moodle.Commenting.InlineTags.InvalidTag
/* Invalid {@param int $value}. */
// @error moodle.Commenting.InlineTags.MissingBraces
// Missing @see braces.
/*
 * Ordinary block comments can have inline violations too.
 * // @error moodle.Commenting.InlineTags.InvalidTag
 * Invalid {@throws Exception}.
 */
// @error moodle.Commenting.InlineTags.InvalidContents
# Invalid {@link not-a-url}.
/* Block comment without a violation. */
// @error moodle.Commenting.InlineTags.InvalidTag
// Invalid {@param int $value} immediately after a block.
// @error moodle.Commenting.InlineTags.InvalidTag
/// Invalid {@param int $value}.
