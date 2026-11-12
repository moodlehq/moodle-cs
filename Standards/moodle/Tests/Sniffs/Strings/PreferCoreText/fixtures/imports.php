<?php

namespace first {
    use function Vendor\text\strlen;
    use function Vendor\text\slice as substr;
    use function Vendor\text\{lower as strtolower, strtoupper};
    use Vendor\text\{function strrev, Thing, const MARKER};
    use Vendor\text\Position as strpos;
    use const Vendor\text\POSITION as strrpos;

    strlen($text);
    STRLEN($text);
    substr($text, 0, 3);
    strtolower($text);
    strtoupper($text);
    strrev($text);
    strpos($text, $needle);
    strrpos($text, $needle);
    \strlen($text);

    $callback = function () use ($text) {
        return strlen($text);
    };

    trait helpers {}
    class example {
        use helpers;
        public function length($text) {
            return strlen($text);
        }
    }
}

namespace second {
    strlen($text);
}

namespace first {
    strlen($text);
}

namespace {
    use function Vendor\text\strlen;
    strlen($text);
    \strlen($text);
}

namespace later {
    strlen($text);
    use function Vendor\text\strlen;
    strlen($text);
}
