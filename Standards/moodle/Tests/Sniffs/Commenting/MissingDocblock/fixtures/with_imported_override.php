<?php

use Override;

/**
 * Base class docblock.
 */
class base_class {
    #[Override]
    public function has_override(): void {}

    public function no_override(): void {}
}

/**
 * Child class docblock.
 */
class child_class extends base_class {
    #[Override]
    public function has_override(): void {}

    public function no_override(): void {}
}
