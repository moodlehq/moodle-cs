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

namespace MoodleHQ\MoodleCS\moodle\Sniffs\Commenting;

use MoodleHQ\MoodleCS\moodle\Util\Docblocks;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHPCSUtils\Utils\FunctionDeclarations;

/**
 * Checks documented parameter counts, names and types against function declarations.
 *
 * @copyright 2026 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class FunctionArgumentsSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array {
        return [T_FUNCTION];
    }

    /**
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The function declaration.
     */
    public function process(File $phpcsFile, $stackPtr): void {
        $docblock = Docblocks::getDocBlockPointer($phpcsFile, $stackPtr);
        if ($docblock === null) {
            // Missing documentation is handled by MissingDocblock.
            return;
        }

        $parameters = FunctionDeclarations::getParameters($phpcsFile, $stackPtr);
        $tags = Docblocks::getMatchingDocTags($phpcsFile, $docblock, '@param');
        if (count($tags) !== count($parameters)) {
            $phpcsFile->addError(
                'Expected %s @param tags for function parameters; found %s.',
                $docblock,
                'ParamCount',
                [count($parameters), count($tags)]
            );
        }

        foreach ($tags as $index => $tag) {
            $content = $this->getTagContent($phpcsFile, $tag, $docblock);
            if (
                // @param content starts with a type, followed by an optional by-reference or variadic variable.
                !preg_match('/^(?<type>\S+)\s+(?<name>&?(?:\.\.\.)?\$\S+)(?:\s|$)/', $content, $parts)
                || in_array('type', explode('|', str_replace('?', 'null|', $parts['type'])), true)
            ) {
                $phpcsFile->addError(
                    'Each @param tag must contain a valid type and parameter name.',
                    $tag,
                    'InvalidParam'
                );
                continue;
            }

            if (!isset($parameters[$index])) {
                continue;
            }

            $parameter = $parameters[$index];
            $name = ltrim($parts['name'], '&.');
            $documentedName = ltrim($parts['name'], '&');
            $expectedName = ($parameter['variable_length'] ? '...' : '') . $parameter['name'];
            if ($documentedName !== $expectedName) {
                $phpcsFile->addError(
                    'Expected @param documentation for "%s"; found "%s".',
                    $tag,
                    'ParamName',
                    [$expectedName, $documentedName]
                );
            }

            if ($parameter['type_hint'] === '') {
                continue;
            }

            $nativeType = $parameter['type_hint'];
            $expected = $this->normaliseType($nativeType);
            $documented = $this->normaliseType($parts['type']);
            if (!in_array('null', explode('|', $expected), true)) {
                // Legacy PHPDoc may include null even without a nullable declaration.
                $documented = implode('|', array_diff(explode('|', $documented), ['null']));
            }
            if ($expected !== $documented) {
                $phpcsFile->addError(
                    'The @param type "%s" for "%s" does not match the declared type "%s".',
                    $tag,
                    'ParamType',
                    [$parts['type'], $name, $nativeType]
                );
            }
        }

        foreach (Docblocks::getMatchingDocTags($phpcsFile, $docblock, '@return') as $tag) {
            // Only the first word is the return type; any following words are its description.
            $type = preg_split('/\s+/', $this->getTagContent($phpcsFile, $tag, $docblock))[0];
            if ($type === '' || in_array('type', explode('|', str_replace('?', 'null|', $type)), true)) {
                $phpcsFile->addError(
                    'The @return tag must contain a valid type.',
                    $tag,
                    'InvalidReturn'
                );
            }
        }
    }

    /**
     * Get tag contents, including continuation lines but not subsequent tags or paragraphs.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $tag The tag token.
     * @param int $docblock The docblock opener.
     * @return string
     */
    private function getTagContent(File $phpcsFile, int $tag, int $docblock): string {
        $tokens = $phpcsFile->getTokens();
        $content = [];
        $lastLine = $tokens[$tag]['line'];
        for ($ptr = $tag + 1; $ptr < $tokens[$docblock]['comment_closer']; $ptr++) {
            if ($tokens[$ptr]['code'] === T_DOC_COMMENT_TAG) {
                break;
            }
            if ($tokens[$ptr]['code'] === T_DOC_COMMENT_STRING) {
                // A blank line ends this tag's content; following prose is a separate paragraph.
                if ($tokens[$ptr]['line'] > $lastLine + 1) {
                    break;
                }
                $content[] = trim($tokens[$ptr]['content']);
                $lastLine = $tokens[$ptr]['line'];
            }
        }
        return implode(' ', $content);
    }

    /**
     * Normalise nullable, union, intersection and typed-array spellings for comparison.
     *
     * As in moodlecheck, class types are compared by their unqualified names.
     *
     * @param string $type The documented or declared type.
     * @return string
     */
    private function normaliseType(string $type): string {
        $type = str_replace(['?', ' '], ['null|', ''], $type);
        $unions = [];
        foreach (explode('|', $type) as $union) {
            $intersections = [];
            foreach (explode('&', trim($union, '()')) as $part) {
                if (substr($part, -2) === '[]') {
                    // This legacy spelling is compared as the generic array type.
                    $part = 'array';
                } else {
                    $separator = strrpos($part, '\\');
                    if ($separator !== false) {
                        $part = substr($part, $separator + 1);
                    }
                }
                $intersections[] = $part;
            }
            // Sort each intersection so equivalent member order compares equally.
            sort($intersections);
            $unions[] = implode('&', $intersections);
        }
        $unions = array_unique($unions);
        // Union order is also immaterial.
        sort($unions);
        return implode('|', $unions);
    }
}
