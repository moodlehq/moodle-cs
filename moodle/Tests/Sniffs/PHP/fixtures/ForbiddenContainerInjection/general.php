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
use DI\Container as PhpDiContainer;
use Psr\Container as PsrContainer;
use Psr\Container\ContainerInterface;
use Psr\Container\ContainerInterface as ContainerAlias;

class valid_consumer {
    public function __construct(\core\clock $clock) {
    }
}

class fully_qualified_consumer {
    public function __construct(\Psr\Container\ContainerInterface $container) {
    }
}

class imported_consumer {
    public function __construct(ContainerInterface $container) {
    }
}

class aliased_consumer {
    public function set_container(ContainerAlias $container): void {
    }
}

class namespace_alias_consumer {
    public function set_container(PsrContainer\ContainerInterface $container): void {
    }
}

class complex_type_consumer {
    public function set_container((ContainerInterface&\Stringable)|null $container): void {
    }
}

class core_di_consumer {
    public function __construct(di $container) {
    }
}

class php_di_consumer {
    public function __construct(PhpDiContainer $container) {
    }
}

function create_from_container(?ContainerAlias $container): void {
}

$closure = function (ContainerInterface $container): void {
};

$arrow = fn(PhpDiContainer $container): PhpDiContainer => $container;

namespace Psr\Container;

class relative_interface_consumer {
    public function __construct(ContainerInterface $container) {
    }
}

namespace core;

class relative_di_consumer {
    public function __construct(di $container) {
    }
}

namespace local_example_fallback;

class parser_edge_consumer {
    public function run($untyped): void {
        $callback = function (local_dependency $dependency): void {
        };
    }
}

$anonymous = new class {
    public function __construct(\Psr\Container\ContainerInterface $container) {
    }
};

namespace Psr\Container;

class namespace_relative_type_consumer {
    public function __construct(namespace\ContainerInterface $container) {
    }
}
