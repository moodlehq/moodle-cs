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

namespace MoodleHQ\MoodleCS\moodle\Util;

use PHP_CodeSniffer\Config;
use PHP_CodeSniffer\Exceptions\DeepExitException;
use PHP_CodeSniffer\Files\DummyFile;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Ruleset;

// phpcs:disable moodle.NamingConventions

/**
 * Utilities related to PHP DocBlocks.
 *
 * @package    local_codechecker
 * @copyright  2024 Andrew Lyons <andrew@nicols.co.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class Docblocks {
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
            return $token;
        } else if ($token['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            return $tokens[$token['comment_opener']];
        } else if (in_array($token['code'], $midDocBlockTokens)) {
            $commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr);
            return $commentStart ? $tokens[$commentStart] : null;
        }

        $find   = [
            T_ABSTRACT   => T_ABSTRACT,
            T_FINAL      => T_FINAL,
            T_READONLY   => T_READONLY,
            T_WHITESPACE => T_WHITESPACE,
        ];

        if ($tokens[$stackPtr]['code'] === T_OPEN_TAG) {
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
                    return $tokens[$stackPtr];
                }
            }

            return null;
        }

        $previousContent = null;
        for ($commentEnd = ($stackPtr - 1); $commentEnd >= 0; $commentEnd--) {
            if (isset($find[$tokens[$commentEnd]['code']]) === true) {
                continue;
            }

            if ($previousContent === null) {
                $previousContent = $commentEnd;
            }

            if (
                $tokens[$commentEnd]['code'] === T_ATTRIBUTE_END
                && isset($tokens[$commentEnd]['attribute_opener']) === true
            ) {
                $commentEnd = $tokens[$commentEnd]['attribute_opener'];
                continue;
            }

            break;
        }

        if ($commentEnd && $tokens[$commentEnd]['code'] === T_DOC_COMMENT_CLOSE_TAG) {
            $opener = $tokens[$commentEnd]['comment_opener'];

            return $tokens[$opener];
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
