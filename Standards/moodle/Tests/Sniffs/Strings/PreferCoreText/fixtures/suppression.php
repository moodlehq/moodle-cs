<?php

// phpcs:ignore moodle.Strings.PreferCoreText.Substr -- Read six bytes for the header.
$header = substr($packet, 0, 6);
$payload = substr($packet, 6); // phpcs:ignore moodle.Strings.PreferCoreText.Substr -- Skip the binary header.

// phpcs:disable moodle.Strings.PreferCoreText.Substr -- Read packet fields by byte offset.
$first = substr($packet, 6, 4);
$second = \substr($packet, 10, 4);
strlen($packet);
// phpcs:enable moodle.Strings.PreferCoreText.Substr

substr($shortname, 0, 253);

// phpcs:ignore moodle.Strings.PreferCoreText.Strlen -- Measure the packet length in bytes.
strlen($packet);
