<?php

namespace first;

use function Vendor\text\strlen;
strlen($text);

namespace second;

strlen($text);

namespace first;

use function \strtoupper as strlen;
strlen($text);
\strlen($text);
