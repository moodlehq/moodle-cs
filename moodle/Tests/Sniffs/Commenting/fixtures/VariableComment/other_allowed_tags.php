<?php

namespace MoodleHQ\MoodleCS\moodle\Tests\Sniffs\PHPUnit;

class other_allowed_tags {
    /**
     * @var int Some deprecated value.
     * @deprecated This variable is deprecated and will be removed in future versions.
     */
    protected int $deprecatedvar;

    /**
     * @var int Some deprecated value.
     * @since Version 1.0.0
     */
    protected int $sincevar;

    /**
     * @var int Some deprecated value.
     * @link https://example.com
     */
    protected int $linkedvar;
}
