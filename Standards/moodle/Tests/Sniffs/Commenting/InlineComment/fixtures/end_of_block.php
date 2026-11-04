<?php

/**
 * Example class.
 */
class Example {
    /**
     * Do something with an end-of-block comment.
     */
    public function doSomething() {
        $result = match ($x) {
            1 => 'a',
            default => 'z',
        }; // End of block comment.

        return $result;
    }
}
