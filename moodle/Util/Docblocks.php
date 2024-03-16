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

namespace MoodleHQ\MoodleCS\moodle\Util;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;
use PHP_CodeSniffer\Util\Tokens;

/**
 * Utilities related to PHP DocBlocks.
 *
 * @copyright  2024 Andrew Lyons <andrew@nicols.co.uk>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class Docblocks
{
    /**
     * Get the docblock for a file, class, interface, trait, or method.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return null|array
     */
    public static function getDocBlock(
        File $phpcsFile,
        int $stackPtr
    ): ?array {
        $docPtr = self::getDocBlockPointer($phpcsFile, $stackPtr);
        if ($docPtr !== null) {
            return $phpcsFile->getTokens()[$docPtr];
        }

        return null;
    }

    /**
     * Get the docblock pointer for a file, class, interface, trait, or method.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return null|int
     */
    public static function getDocBlockPointer(
        File $phpcsFile,
        int $stackPtr
    ): ?int {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        // Check if the passed pointer was for a doc.
        $midDocBlockTokens = [
            T_DOC_COMMENT,
            T_DOC_COMMENT_STAR,
            T_DOC_COMMENT_WHITESPACE,
            T_DOC_COMMENT_TAG,
            T_DOC_COMMENT_STRING,
        ];
        if ($token['code'] === T_DOC_COMMENT_OPEN_TAG) {
            return $stackPtr;
        } elseif ($token['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            // The pointer was for a close tag. Fetch the corresponding open tag.
            return $token['comment_opener'];
        } elseif (in_array($token['code'], $midDocBlockTokens)) {
            // The pointer was for a token inside the docblock. Fetch the corresponding open tag.
            $commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr);
            return $commentStart ?: null;
        }

        // If the pointer was for a file, fetch the doc tag from the open tag.
        if ($tokens[$stackPtr]['code'] === T_OPEN_TAG) {
            return self::getDocTagFromOpenTag($phpcsFile, $stackPtr);
        }

        // Assume that the stackPtr is for a class, interface, trait, or method, or some part of them.
        // Back track over each previous pointer until we find the docblock.
        // It should be on the line immediately before the pointer.
        $pointerLine = $tokens[$stackPtr]['line'];

        $previousContent = null;
        for ($commentEnd = ($stackPtr - 1); $commentEnd >= 0; $commentEnd--) {
            $token = $tokens[$commentEnd];
            if ($previousContent === null) {
                $previousContent = $commentEnd;
            }

            if ($token['code'] === T_ATTRIBUTE_END && isset($token['attribute_opener'])) {
                $commentEnd = $token['attribute_opener'];
                $pointerLine = $token['line'];
                continue;
            }

            if ($token['line'] < ($pointerLine - 1)) {
                // The comment msut be on the line immediately before the pointer, or immediately before the attribute.       z
                return null;
            }

            if ($token['code'] === T_DOC_COMMENT_CLOSE_TAG) {
                // The pointer was for a close tag. Fetch the corresponding open tag.
                return $token['comment_opener'];
            }
        }

        return null; // @codeCoverageIgnore
    }

    /**
     * Get the doc tag from the file open tag.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return null|int
     */
    protected static function getDocTagFromOpenTag(
        File $phpcsFile,
        int $stackPtr
    ): ?int {
        $tokens = $phpcsFile->getTokens();

        $ignore = [
            T_WHITESPACE,
            T_COMMENT,
        ];

        $stopAtTypes = [
            T_CLASS,
            T_INTERFACE,
            T_TRAIT,
            T_ENUM,
            T_FUNCTION,
            T_CLOSURE,
            T_PUBLIC,
            T_PRIVATE,
            T_PROTECTED,
            T_FINAL,
            T_STATIC,
            T_ABSTRACT,
            T_READONLY,
            T_CONST,
            T_PROPERTY,
            T_INCLUDE,
            T_INCLUDE_ONCE,
            T_REQUIRE,
            T_REQUIRE_ONCE,
        ];

        while ($stackPtr = $phpcsFile->findNext($ignore, ($stackPtr + 1), null, true)) {
            if ($tokens[$stackPtr]['code'] === T_NAMESPACE || $tokens[$stackPtr]['code'] === T_USE) {
                $stackPtr = $phpcsFile->findNext(T_SEMICOLON, $stackPtr + 1);
                continue;
            }

            if ($tokens[$stackPtr]['code'] === T_DOC_COMMENT_OPEN_TAG) {
                $nextToken = $tokens[$stackPtr]['comment_closer'];
                while ($nextToken = $phpcsFile->findNext(T_WHITESPACE, $nextToken + 1, null, true)) {
                    if ($nextToken && $tokens[$nextToken]['code'] === T_ATTRIBUTE) {
                        $nextToken = $tokens[$nextToken]['attribute_closer'] + 1;
                        continue;
                    }
                    if (in_array($tokens[$nextToken]['code'], $stopAtTypes)) {
                        return null;
                    }
                    break;
                }
                return $stackPtr;
            }
        }

        return null;
    }

    public static function getMatchingDocTags(
        File $phpcsFile,
        int $stackPtr,
        string $tagName
    ): array {
        $tokens = $phpcsFile->getTokens();
        $docblock = self::getDocBlock($phpcsFile, $stackPtr);
        if ($docblock === null) {
            return [];
        }

        $matchingTags = [];
        foreach ($docblock['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === $tagName) {
                $matchingTags[] = $tag;
            }
        }

        return $matchingTags;
    }
}
