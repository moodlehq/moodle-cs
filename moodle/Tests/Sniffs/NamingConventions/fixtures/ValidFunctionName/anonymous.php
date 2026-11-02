<?php

$example = new class extends parent_class {
    public function BadMethod() {}
    public function __notmagic() {}
    public function valid_method() {}
    public function __construct() {}
    public function setUp() {}
    #[\Override]
    public function inheritedMethod() {}
};
