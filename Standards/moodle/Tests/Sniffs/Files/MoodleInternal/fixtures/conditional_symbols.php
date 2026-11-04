<?php

if (!class_exists('example')) {
    class example {}
} elseif (false) {
    function fallback_example() {}
} else if (false) {
    function other_example() {}
} else {
    function final_example() {}
}
