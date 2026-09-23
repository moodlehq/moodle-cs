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

namespace local_example;

use core\di;
use core\di as moodle_di;
use core as moodle_core;

function global_composition_root(): void {
    \core\di::get(service::class);
}

class valid_service {
    public function get(): void {
    }

    public function run(): void {
        unrelated\di::get(service::class);
        di::other_method();
    }
}

class service_locator {
    public function run(): void {
        \core\di::get(service::class);
        di::get(service::class);
        moodle_di::GET(service::class);
        moodle_core\di::get_container();
    }
}

trait service_locator_trait {
    public function run(): void {
        \core\di::get(service::class);
    }
}

class nested_lookup {
    public function run(): void {
        $callback = fn() => \core\di::get(service::class);
    }
}

class intentional_boundary {
    public function run(): void {
        // phpcs:ignore MoodleExtra.PHP.DiscouragedContainerLookup.InClass -- Intentional composition root.
        \core\di::get(service::class);
    }
}

$anonymous = new class {
    public function run(): void {
        \core\di::get(service::class);
    }
};

namespace core;

class relative_lookup {
    public function run(): void {
        di::get(\stdClass::class);
        namespace\di::get_container();
    }
}

namespace local_example;

class static_syntax {
    public function run(): void {
        \core\di::get;
        $class = \core\di::class;
        $class::get(service::class);
    }
}
