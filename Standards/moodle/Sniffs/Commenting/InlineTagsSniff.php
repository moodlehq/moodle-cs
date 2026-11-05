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

/**
 * Validates inline PHPDoc tags, their braces and their targets.
 *
 * @copyright 2026 onwards Andrew Lyons <andrew@nicols.co.uk>
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class InlineTagsSniff implements Sniff
{
    /**
     * @return int[]
     */
    public function register(): array {
        return [T_DOC_COMMENT_OPEN_TAG, T_COMMENT];
    }

    /**
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The comment token.
     * @return int|null
     */
    public function process(File $phpcsFile, $stackPtr): ?int {
        $tokens = $phpcsFile->getTokens();
        $content = '';
        $textPointers = [];
        $next = null;
        if ($tokens[$stackPtr]['code'] === T_COMMENT) {
            $block = strpos($tokens[$stackPtr]['content'], '/*') === 0;
            $ptr = $stackPtr;
            do {
                $text = $tokens[$ptr]['content'];
                if ($block) {
                    // Strip block-comment delimiters and line prefixes before scanning the comment text.
                    $text = preg_replace($ptr === $stackPtr ? '~^\s*/\*+~' : '~^\s*\*~', '', $text);
                    $text = preg_replace('~\*/\s*$~', '', $text);
                    $text = preg_replace('/^\s*@/', '', $text);
                } elseif (strpos($text, '///') === 0) {
                    $text = preg_replace('/^\s*@/', '', substr($text, 3));
                }
                $textPointers[] = ['offset' => strlen($content), 'ptr' => $ptr];
                $content .= $text;
                if (!$block || strpos($tokens[$ptr]['content'], '*/') !== false) {
                    break;
                }
                $ptr++;
            } while (isset($tokens[$ptr]) && $tokens[$ptr]['code'] === T_COMMENT);
            if ($block) {
                $next = $ptr + 1;
            }
        } else {
            for ($ptr = $stackPtr + 1; $ptr < $tokens[$stackPtr]['comment_closer']; $ptr++) {
                if ($tokens[$ptr]['code'] === T_DOC_COMMENT_TAG) {
                    // Treat a following tag as link/see description text only if that inline tag is still open.
                    if (preg_match('/\{@(?:link|see)\b\s*[^\s{}][^{}]*$/s', $content)) {
                        $content .= "\n";
                        $textPointers[] = ['offset' => strlen($content), 'ptr' => $ptr];
                        $content .= $tokens[$ptr]['content'];
                    } else {
                        $content .= "\n{";
                    }
                } elseif ($tokens[$ptr]['code'] === T_DOC_COMMENT_STRING) {
                    $content .= "\n";
                    $textPointers[] = ['offset' => strlen($content), 'ptr' => $ptr];
                    $content .= $tokens[$ptr]['content'];
                }
            }
        }
        // Ignore @ characters embedded in names, paths, or URLs; retain braces and tag names separately.
        preg_match_all(
            '/(?<full>(?<brace>\{)?(?<![\w\\\\.\/:+%-])@(?<handle>[a-zA-Z][a-zA-Z0-9-]*))/',
            $content,
            $matches,
            PREG_OFFSET_CAPTURE
        );
        $skipUntil = 0;
        $textIndex = 0;
        foreach ($matches['full'] as $index => [$match, $offset]) {
            if ($offset < $skipUntil) {
                continue;
            }
            while (isset($textPointers[$textIndex + 1]) && $textPointers[$textIndex + 1]['offset'] <= $offset) {
                $textIndex++;
            }
            $errorPtr = $textPointers[$textIndex]['ptr'];
            $tag = $matches['handle'][$index][0];
            if (!preg_match('/^[a-z-]+$/', $tag) || !Docblocks::isKnownTag($tag)) {
                continue;
            }
            if ($tag !== 'link' && $tag !== 'see') {
                $phpcsFile->addError(
                    'Invalid inline PHPDoc tag "@%s"; only @link and @see are allowed inline.',
                    $errorPtr,
                    'InvalidTag',
                    [$tag]
                );
                continue;
            }

            $tail = substr($content, $offset + strlen($match));
            $end = strcspn($tail, '{}');
            if ($matches['brace'][$index][0] !== '{' || !isset($tail[$end]) || $tail[$end] !== '}') {
                $phpcsFile->addError(
                    'Inline PHPDoc tag "@%s" must be enclosed in curly braces.',
                    $errorPtr,
                    'MissingBraces',
                    [$tag]
                );
                continue;
            }

            $skipUntil = $offset + strlen($match) + $end + 1;
            $target = preg_split('/\s+/', trim(substr($tail, 0, $end)))[0];
            if ($tag === 'link') {
                $valid = filter_var($target, FILTER_VALIDATE_URL) !== false;
            } else {
                // Count punctuation used by PHPDoc element references as part of the same target word.
                $valid = str_word_count($target, 0, '\()-_:>$0123456789') === 1;
            }

            if (!$valid) {
                $phpcsFile->addError(
                    'Inline PHPDoc tag "@%s" must contain %s, optionally followed by a description.',
                    $errorPtr,
                    'InvalidContents',
                    [$tag, $tag === 'link' ? 'a valid URL' : 'a structural element reference']
                );
            }
        }
        return $next;
    }
}
