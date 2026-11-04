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

namespace MoodleHQ\MoodleCS\moodle\Tests\Util;

use MoodleHQ\MoodleCS\moodle\Tests\MoodleCSBaseTestCase;
use MoodleHQ\MoodleCS\moodle\Util\NamespaceScopeUtil;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Ruleset;
use PHPCSUtils\Internal\Cache;

/**
 * Test the Class name specific moodle utilities class
 *
 * @copyright  onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Util\NamespaceScopeUtil
 */
class NamespaceScopeUtilTest extends MoodleCSBaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Cache::clear();
    }

    /**
     * @dataProvider validTagsProvider
     */
    public function testClassnames(
        string $content,
        $stackPtrSearch,
        string $classname,
        string $expectedQualifiedName
    ): void {
        $config = new Config([]);
        $ruleset = new Ruleset($config);

        $phpcsFile = new DummyFile($content, $ruleset, $config);
        $phpcsFile->process();

        $searchPtr = $phpcsFile->findNext($stackPtrSearch, 0);

        $this->assertEquals(
            $expectedQualifiedName,
            NamespaceScopeUtil::getQualifiedName($phpcsFile, $searchPtr, $classname)
        );
    }

    public static function validTagsProvider(): \Generator {
        yield 'No namespace or imports' => [
            '<?php
            class Example {}',
            T_CLASS,
            'Example',
            'Example',
        ];
        yield 'Namespaced class' => [
            '<?php
            namespace MyNamespace;
            class Example {}',
            T_CLASS,
            'Example',
            'MyNamespace\Example',
        ];
        yield 'Qualified class' => [
            '<?php
            class AnotherExample extends \Example {}',
            T_CLASS,
            '\Example',
            'Example',
        ];
        yield 'Imported class' => [
            '<?php
            use MyNamespace\Example;
            class AnotherExample extends Example {}',
            T_CLASS,
            'Example',
            'MyNamespace\Example',
        ];
        yield 'Nested namespaced interface class' => [
            '<?php
            namespace MyNamespace;
            class AnotherExample extends Nested\Example {}',
            T_CLASS,
            'Nested\Example',
            'MyNamespace\Nested\Example',
        ];
        yield 'Imported classes' => [
            '<?php
            use MyNamespace\{
                AnotherExample,
                Example
            };
            class AnotherExample extends Example {}',
            T_CLASS,
            'Example',
            'MyNamespace\Example',
        ];
        yield 'Imported classes listed after usage' => [
            '<?php
            class AnotherExample extends Example {}
            use MyNamespace\{
                AnotherExample,
                Example
            };',
            T_CLASS,
            'Example',
            'Example',
        ];

        $builtins = [
            'int',
            'float',
            'string',
            'bool',
            'array',
            'object',
            'callable',
            'iterable',
        ];
        foreach ($builtins as $type) {
            yield "Built-in types: {$type}" => [
                "<?php
                namespace MyNamespace;
                interface Example {
                    public function exampleFunction({$type} \$param): string;
                }",
                T_VARIABLE,
                $type,
                $type,
            ];
        }

        $notbuiltins = [
            'boolean',
            'countable',
            'Iterator',
            'Traversable',
        ];
        foreach ($notbuiltins as $type) {
            yield "Non built-in types: {$type}" => [
                "<?php
                namespace MyNamespace;
                interface Example {
                    public function exampleFunction({$type} \$param): string;
                }",
                T_VARIABLE,
                $type,
                "MyNamespace\\{$type}",
            ];
        }
    }

    /**
     * @dataProvider getClassImportsProvider
     */
    public function testGetClassImports(
        string $content,
        $stackPtrSearch,
        array $imports
    ): void {
        $config = new Config([]);
        $ruleset = new Ruleset($config);

        $phpcsFile = new DummyFile($content, $ruleset, $config);
        $phpcsFile->process();

        $searchPtr = $phpcsFile->findNext($stackPtrSearch, 0);

        $this->assertEquals(
            $imports,
            NamespaceScopeUtil::getClassImports($phpcsFile, $searchPtr)
        );
    }

    public static function getClassImportsProvider(): \Generator {
        yield 'Simple import' => [
            '<?php
            use Example\Thing\Example;
            class Example extends AnotherExample {}
            use Example\Thing\AnotherExample;
            ',
            T_CLASS,
            ['Example' => \Example\Thing\Example::class],
        ];
        yield 'Import with alias' => [
            '<?php
            use Example\Thing\Example as OtherExample;
            class Example extends AnotherExample {}
            use Example\Thing\AnotherExample;
            ',
            T_CLASS,
            ['OtherExample' => \Example\Thing\Example::class],
        ];
        yield 'No import' => [
            '<?php
            class Example extends AnotherExample {}
            use Example\Thing\AnotherExample;
            ',
            T_CLASS,
            [],
        ];
        yield 'Unrelated import' => [
            '<?php
            use function My\Full\functionName;
            use function My\Full\functionName as func;
            use const My\Full\CONSTANT;
            class Example extends AnotherExample {}
            use Example\Thing\AnotherExample;
            ',
            T_CLASS,
            [],
        ];
        yield 'Grouped import' => [
            '<?php
            use My\Full\Classname as Another, My\Full\NSname;
            use some\name\space\{ClassA, ClassB, ClassC as C};
            class Example extends AnotherExample {}
            use Example\Thing\AnotherExample;
            ',
            T_CLASS,
            [
                'Another' => \My\Full\Classname::class,
                'NSname' => \My\Full\NSname::class,
                'ClassA' => \some\name\space\ClassA::class,
                'ClassB' => \some\name\space\ClassB::class,
                'C' => \some\name\space\ClassC::class,
            ],
        ];
    }
}
