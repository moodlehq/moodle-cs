<?php

namespace example;

use function tools\substr as tools_substr;

function substr($text, $start, $length) {
    return $text;
}

function & /* Reference return. */ strlen($text) {
    return $text;
}

class example {
    public function &strtolower($text) {
        return $text;
    }

    public function substr($text, $start, $length) {
        return $text;
    }
}

core_text::substr($text, 0, 6);
\core_text::substr($text, 0, 6);
$object->substr($text, 0, 6);
$object?->substr($text, 0, 6);
example::substr($text, 0, 6);
mb_substr($text, 0, 6, 'UTF-8');
core_text::strlen($text);
core_text::strtolower($text);
core_text::strtoupper($text);
core_text::strpos($text, $needle);
core_text::strrpos($text, $needle);
core_text::strrchr($text, $needle);
core_text::strrev($text);
str_replace('_', '-', $text);
trim($text);
ucwords($text);
new substr($text);
$literal = 'substr($text, 0, 6)';
// substr($text, 0, 6);
