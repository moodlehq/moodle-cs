<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

defined('MOODLE_INTERNAL') || die();

/**
 * A dummy class for tests of rules involving properties.
 */
class dummy_with_properties {

    /**
     * @var mixed $documented1 I'm just a dummy!
     */
    var $documented1;
    /**
     * @var ?string $documented2 I'm just a dummy!
     */
    var mixed $documented2;
    /**
     * @var mixed $documented3 I'm just a dummy!
     */
    private $documented3;
    /**
     * @var ?string $documented4 I'm just a dummy!
     */
    private ?string $documented4;

    /**
     * @var A correctly documented constant.
     */
    const CORRECTLY_DOCUMENTED_CONSTANT = 0;
}
