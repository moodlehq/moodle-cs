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

namespace core\router;

use Psr\Container\ContainerInterface;

class bridge {
    public static function create(?ContainerInterface $container = null): void {
    }
}

class controller_invoker {
    public function __construct(ContainerInterface $container) {
    }
}

class response_handler {
    public function __construct(ContainerInterface $container) {
    }
}

namespace core;

use Psr\Container\ContainerInterface;

class di {
    public static function replace(ContainerInterface $container): void {
    }
}
