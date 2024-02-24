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
use MoodleHQ\MoodleCS\moodle\Util\Docblocks;
use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Ruleset;

/**
 * Test the Docblocks specific moodle utilities class
 *
 * @copyright  2021 onwards Eloy Lafuente (stronk7) {@link https://stronk7.com}
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @covers \MoodleHQ\MoodleCS\moodle\Util\Docblocks
 */
class DocblocksTest extends MoodleCSBaseTestCase
{
    public function testGetDocBlock(): void {
        $phpcsConfig = new Config();
        $phpcsRuleset = new Ruleset($phpcsConfig);
        $phpcsFile = new \PHP_CodeSniffer\Files\LocalFile(
            __DIR__ . '/fixtures/docblocks/none.php',
            $phpcsRuleset,
            $phpcsConfig
        );

        $phpcsFile->process();
        $filePointer = $phpcsFile->findNext(T_OPEN_TAG, 0);

        $docBlock = Docblocks::getDocBlock($phpcsFile, $filePointer);
        $this->assertNull($docBlock);
    }

    public function testGetDocBlockTags(): void {
        $phpcsConfig = new Config();
        $phpcsRuleset = new Ruleset($phpcsConfig);
        $phpcsFile = new \PHP_CodeSniffer\Files\LocalFile(
            __DIR__ . '/fixtures/docblocks/class_docblock.php',
            $phpcsRuleset,
            $phpcsConfig
        );

        $phpcsFile->process();
        $filePointer = $phpcsFile->findNext(T_OPEN_TAG, 0);
        $classPointer = $phpcsFile->findNext(T_CLASS, 0);

        $fileDocBlock = Docblocks::getDocBlock($phpcsFile, $filePointer);
        $this->assertNotNull($fileDocBlock);
        $this->assertCount(1, Docblocks::getMatchingDocTags($phpcsFile, $filePointer, '@copyright'));
        $this->assertCount(0, Docblocks::getMatchingDocTags($phpcsFile, $filePointer, '@property'));

        $classDocBlock = Docblocks::getDocBlock($phpcsFile, $classPointer);
        $this->assertNotNull($classDocBlock);
        $this->assertNotEquals($fileDocBlock, $classDocBlock);
        $this->assertCount(1, Docblocks::getMatchingDocTags($phpcsFile, $classPointer, '@copyright'));
        $this->assertCount(2, Docblocks::getMatchingDocTags($phpcsFile, $classPointer, '@property'));

        $methodPointer = $phpcsFile->findNext(T_FUNCTION, $classPointer);
        $this->assertNull(Docblocks::getDocBlock($phpcsFile, $methodPointer));
        $this->assertCount(0, Docblocks::getMatchingDocTags($phpcsFile, $methodPointer, '@property'));

        // Get the docblock from pointers at the start, middle, and end, of a docblock.
        $tokens = $phpcsFile->getTokens();
        $startDocPointer = $phpcsFile->findNext(T_DOC_COMMENT_OPEN_TAG, 0);
        $endDocPointer = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $startDocPointer);
        $middleDocPointer = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $startDocPointer, $endDocPointer);

        $docblock = Docblocks::getDocBlock($phpcsFile, $startDocPointer);
        $this->assertIsArray($docblock);
        $this->assertEquals($tokens[$startDocPointer], $docblock);

        $docblock = Docblocks::getDocBlock($phpcsFile, $middleDocPointer);
        $this->assertIsArray($docblock);
        $this->assertEquals($tokens[$startDocPointer], $docblock);

        $docblock = Docblocks::getDocBlock($phpcsFile, $endDocPointer);
        $this->assertIsArray($docblock);
        $this->assertEquals($tokens[$startDocPointer], $docblock);
    }

    public function testGetDocBlockClassOnly(): void {
        $phpcsConfig = new Config();
        $phpcsRuleset = new Ruleset($phpcsConfig);
        $phpcsFile = new \PHP_CodeSniffer\Files\LocalFile(
            __DIR__ . '/fixtures/docblocks/class_docblock_only.php',
            $phpcsRuleset,
            $phpcsConfig
        );

        $phpcsFile->process();
        $filePointer = $phpcsFile->findNext(T_OPEN_TAG, 0);
        $classPointer = $phpcsFile->findNext(T_CLASS, 0);

        $fileDocBlock = Docblocks::getDocBlock($phpcsFile, $filePointer);
        $this->assertNull($fileDocBlock);

        $classDocBlock = Docblocks::getDocBlock($phpcsFile, $classPointer);
        $this->assertNotNull($classDocBlock);
        $this->assertNotEquals($fileDocBlock, $classDocBlock);
        $this->assertCount(1, Docblocks::getMatchingDocTags($phpcsFile, $classPointer, '@copyright'));
        $this->assertCount(2, Docblocks::getMatchingDocTags($phpcsFile, $classPointer, '@property'));

        $methodPointer = $phpcsFile->findNext(T_FUNCTION, $classPointer);
        $this->assertNull(Docblocks::getDocBlock($phpcsFile, $methodPointer));
        $this->assertCount(0, Docblocks::getMatchingDocTags($phpcsFile, $methodPointer, '@property'));
    }
}
