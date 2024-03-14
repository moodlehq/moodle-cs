<?php

// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANdTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace MoodleHQ\MoodleCS\moodle\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractVariableSniff;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\ObjectDeclarations;

/**
 * Parses and verifies the variable doc comment.
 *
 * The Sniff is based upon the Squiz Labs version, but it has been modified to accept int, rather than integer.
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author Andrew Lyons <andrew@nicols.co.uk>
 * @copyright 2006-2015 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/PHPCSStandards/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */
class VariableCommentSniff extends AbstractVariableSniff
{
    /**
     * An array of variable types for param/var we will check.
     *
     * @var string[]
     */
    protected static $allowedTypes = [
        'array',
        'bool',
        'float',
        'int',
        'mixed',
        'object',
        'string',
        'resource',
        'callable',
    ];

    /**
     * Called to process class member vars.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function processMemberVar(File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();

        $ignore = [
            T_WHITESPACE => T_WHITESPACE,
            T_NULLABLE => T_NULLABLE,
        ]
            + Collections::propertyModifierKeywords()
            + Collections::parameterTypeTokens();

        for ($commentEnd = ($stackPtr - 1); $commentEnd >= 0; $commentEnd--) {
            if (isset($ignore[$tokens[$commentEnd]['code']]) === true) {
                continue;
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

        if (
            $commentEnd === false
            || ($tokens[$commentEnd]['code'] !== T_DOC_COMMENT_CLOSE_TAG
                && $tokens[$commentEnd]['code'] !== T_COMMENT)
        ) {
            $phpcsFile->addError('Missing member variable doc comment', $stackPtr, 'Missing');
            return;
        }

        if ($tokens[$commentEnd]['code'] === T_COMMENT) {
            $phpcsFile->addError('You must use "/**" style comments for a member variable comment', $stackPtr, 'WrongStyle');
            return;
        }

        $commentStart = $tokens[$commentEnd]['comment_opener'];

        $foundVar = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@var') {
                if ($foundVar !== null) {
                    $error = 'Only one @var tag is allowed in a member variable comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateVar');
                } else {
                    $foundVar = $tag;
                }
            } elseif ($tokens[$tag]['content'] === '@see') {
                // Make sure the tag isn't empty.
                $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $tag, $commentEnd);
                if ($string === false || $tokens[$string]['line'] !== $tokens[$tag]['line']) {
                    $error = 'Content missing for @see tag in member variable comment';
                    $phpcsFile->addError($error, $tag, 'EmptySees');
                }
            } else {
                $error = '%s tag is not allowed in member variable comment';
                $data = [$tokens[$tag]['content']];
                $phpcsFile->addWarning($error, $tag, 'TagNotAllowed', $data);
            }
        }

        // The @var tag is the only one we require.
        if ($foundVar === null) {
            $error = 'Missing @var tag in member variable comment';
            $phpcsFile->addError($error, $commentEnd, 'MissingVar');
            return;
        }

        $firstTag = $tokens[$commentStart]['comment_tags'][0];
        if ($foundVar !== null && $tokens[$firstTag]['content'] !== '@var') {
            $error = 'The @var tag must be the first tag in a member variable comment';
            $phpcsFile->addError($error, $foundVar, 'VarOrder');
        }

        // Make sure the tag isn't empty and has the correct padding.
        $string = $phpcsFile->findNext(T_DOC_COMMENT_STRING, $foundVar, $commentEnd);
        if ($string === false || $tokens[$string]['line'] !== $tokens[$foundVar]['line']) {
            $error = 'Content missing for @var tag in member variable comment';
            $phpcsFile->addError($error, $foundVar, 'EmptyVar');
            return;
        }

        // Support both a var type and a description.
        preg_match('`^((?:\|?(?:array\([^\)]*\)|[\\\\a-z0-9\[\]]+))*)( .*)?`i', $tokens[($foundVar + 2)]['content'], $varParts);
        $varType = $varParts[1];

        // Check var type (can be multiple, separated by '|').
        $typeNames = explode('|', $varType);
        $suggestedNames = [];
        foreach ($typeNames as $i => $typeName) {
            $suggestedName = self::suggestType($typeName);
            if (in_array($suggestedName, $suggestedNames, true) === false) {
                $suggestedNames[] = $suggestedName;
            }
        }

        $suggestedType = implode('|', $suggestedNames);
        if ($varType !== $suggestedType) {
            $error = 'Expected "%s" but found "%s" for @var tag in member variable comment';
            $data = [
                $suggestedType,
                $varType,
            ];

            $fix = $phpcsFile->addFixableError($error, $foundVar, 'IncorrectVarType', $data);
            if ($fix === true) {
                $replacement = $suggestedType;
                if (empty($varParts[2]) === false) {
                    $replacement .= $varParts[2];
                }

                $phpcsFile->fixer->replaceToken(($foundVar + 2), $replacement);
                unset($replacement);
            }
        }
    }

    /**
     * Processes normal variables within a method.
     *
     * @param File $file The file where this token was found.
     * @param int $stackptr The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(File $phpcsFile, $stackPtr) {
        // Find the method that this variable is declared in.
        $methodPtr = $phpcsFile->findPrevious(T_FUNCTION, $stackPtr);
        if ($methodPtr === false) {
            // Not in a method.
            return;  // @codeCoverageIgnore
        }

        $methodName = ObjectDeclarations::getName($phpcsFile, $methodPtr);
        if ($methodName !== '__construct') {
            // Not in a constructor.
            return;
        }

        $method = $phpcsFile->getTokens()[$methodPtr];
        if ($method['parenthesis_opener'] < $stackPtr && $method['parenthesis_closer'] > $stackPtr) {
            $this->processMemberVar($phpcsFile, $stackPtr);
            return;
        }
    }

    /**
     * Returns a valid variable type for param/var tags.
     *
     * If type is not one of the standard types, it must be a custom type.
     * Returns the correct type name suggestion if type name is invalid.
     *
     * @param string $varType The variable type to process.
     *
     * @return string
     */
    protected static function suggestType(string $varType): string {
        if (in_array($varType, self::$allowedTypes, true) === true) {
            return $varType;
        } elseif (substr($varType, -2) === '[]') {
            return sprintf(
                '%s[]',
                self::suggestType(substr($varType, 0, -2))
            );
        } else {
            $lowerVarType = strtolower($varType);
            switch ($lowerVarType) {
                case 'bool':
                case 'boolean':
                    return 'bool';
                case 'double':
                case 'real':
                case 'float':
                    return 'float';
                case 'int':
                case 'integer':
                    return 'int';
                case 'array()':
                case 'array':
                    return 'array';
            }

            if (strpos($lowerVarType, 'array(') !== false) {
                // Valid array declaration:
                // array, array(type), array(type1 => type2).
                $matches = [];
                $pattern = '/^array\(\s*([^\s^=^>]*)(\s*=>\s*(.*))?\s*\)/i';
                if (preg_match($pattern, $varType, $matches) !== 0) {
                    $type1 = '';
                    if (isset($matches[1]) === true) {
                        $type1 = $matches[1];
                    }

                    $type2 = '';
                    if (isset($matches[3]) === true) {
                        $type2 = $matches[3];
                    }

                    $type1 = self::suggestType($type1);
                    $type2 = self::suggestType($type2);

                    // Note: The phpdoc array syntax only allows you to describe the array value type.
                    // https://docs.phpdoc.org/latest/guide/guides/types.html#arrays
                    if ($type1 && !$type2) {
                        // This is an array of [type2, type2, type2].
                        return "{$type1}[]";
                    }
                    // This is an array of [type1 => type2, type1 => type2, type1 => type2].
                    return "{$type2}[]";
                } else {
                    return 'array';
                }
            } elseif (in_array($lowerVarType, self::$allowedTypes, true) === true) {
                // A valid type, but not lower cased.
                return $lowerVarType;
            } else {
                // Must be a custom type name.
                return $varType;
            }
        }
    }

    /**
     * @codeCoverageIgnore
     */
    protected function processVariableInString(File $phpcsFile, $stackPtr) {
    }
}
