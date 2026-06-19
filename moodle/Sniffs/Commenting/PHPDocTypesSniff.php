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

use MoodleHQ\MoodleCS\moodle\Util\Docblocks;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Checks PHPDoc types.
 *
 * @copyright  2026 James Calder and Otago Polytechnic
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PHPDocTypesSniff implements Sniff
{
    /**
     * @var array[] Types
     */
    protected const TYPES = [
        'array' => ['extends' => ['iterable']],
        'bool' => ['extends' => []],
        'callable' => ['extends' => []],
        'false' => ['extends' => ['bool']],
        'float' => ['extends' => []],
        'int' => ['extends' => []],
        'iterable' => ['extends' => []],
        'mixed' => ['extends' => []],
        'never' => ['extends' => []], // Actually everything.
        'null' => ['extends' => []],
        'object' => ['extends' => []],
        'parent' => ['extends' => ['object']],
        'resource' => ['extends' => []],
        'self' => ['extends' => ['parent', 'object']],
        'static' => ['extends' => ['self', 'parent', 'object']],
        'string' => ['extends' => []],
        'true' => ['extends' => ['bool']],
        'void' => ['extends' => []], // Not even mixed.
        '$this' => ['extends' => ['static', 'self', 'parent', 'object']],
    ];

    /**
     * Register for class tags.
     *
     * @return array
     */
    public function register() {
        return [
            T_FUNCTION,
        ];
    }

    /**
     * Processes PHP files and perform PHPDoc type checks.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The position in the stack.
     * @return int|null
     */
    public function process(File $phpcsFile, $stackPtr): ?int {
        return $this->processFunction($phpcsFile, $stackPtr);
    }

    /**
     * Processes a function.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The position in the stack.
     * @return int|null
     */
    protected function processFunction(File $phpcsFile, int $stackPtr): ?int {
        $tokens = $phpcsFile->getTokens();

        // Get Doc Block.
        $docBlockPtr = Docblocks::getDocBlockPointer($phpcsFile, $stackPtr);
        if ($docBlockPtr === null) {
            // No DocBlock for this function.
            return null;
        }

        // Get native parameters.
        $nativeParams = $phpcsFile->getMethodParameters($stackPtr);
        foreach ($nativeParams as $index => $nativeParam) {
            $nativeParams[$index]['type_hint'] = $this->simplifyType($nativeParam['type_hint']);
        }

        // Get Doc parameters.
        $docParams = [];
        $docParamTagPtrs = Docblocks::getMatchingDocTags($phpcsFile, $docBlockPtr, '@param');
        foreach ($docParamTagPtrs as $docParamTagPtr) {
            $docParamToken = $tokens[$docParamTagPtr + 2] ?? null;
            $docParamString = ($docParamToken && $docParamToken['code'] == T_DOC_COMMENT_STRING) ?
                $docParamToken['content'] : '';
            $docParamString = preg_replace('/\\s+/', ' ', trim($docParamString)) . '  ';
            $docParam2ndSpace = strpos($docParamString, ' ', strpos($docParamString, ' ') + 1);
            $docParamString = substr($docParamString, 0, $docParam2ndSpace);
            $docParamString = preg_replace('/ &\\.\\.\\.| &| \\.\\.\\./', ' ', $docParamString);
            $docParamArray = explode(' ', $docParamString);
            $docParams[] = [
                'type_hint' => $this->simplifyType($docParamArray[0]),
                'name' => $docParamArray[1],
            ];
        }

        // Check parameters match.
        for ($index = 0; $index < min(count($docParams), count($nativeParams)); $index++) {
            $indexForUser = $index + 1;
            $docParam = $docParams[$index];
            $nativeParam = $nativeParams[$index];
            if ($nativeParam['type_hint'] !== null) {
                // We only do any checks when there is a valid native type at the moment,
                // to avoid producing more errors than the previous checker.
                if ($docParam['type_hint'] === null) {
                    $phpcsFile->addError(
                        "PHPDoc function parameter {$indexForUser} type doesn't conform to PHP-FIG PSR-5",
                        $docBlockPtr,
                        'PHPDocFunParamTypePHPFIG'
                    );
                } elseif (!$this->typeMatch($docParam['type_hint'], $nativeParam['type_hint'])) {
                    $phpcsFile->addError(
                        "PHPDoc function parameter {$indexForUser} type mismatch",
                        $docBlockPtr,
                        'PHPDocFunParamTypeMismatch'
                    );
                }
            }
            if ($docParam['name'] != $nativeParam['name']) {
                $phpcsFile->addError(
                    "PHPDoc function parameter {$indexForUser} name mismatch",
                    $docBlockPtr,
                    'PHPDocFunParamNameMismatch'
                );
            }
        }
        if (count($docParams) != count($nativeParams)) {
            $phpcsFile->addError(
                "PHPDoc function parameter count mismatch",
                $docBlockPtr,
                'PHPDocFunParamCountMismatch'
            );
        }

        // Check return tags have types.
        $docRetTagPtrs = Docblocks::getMatchingDocTags($phpcsFile, $docBlockPtr, '@return');
        foreach ($docRetTagPtrs as $docRetTagPtr) {
            $docRetToken = $tokens[$docRetTagPtr + 2] ?? null;
            $docRetString = ($docRetToken && $docRetToken['code'] == T_DOC_COMMENT_STRING) ?
                $docRetToken['content'] : '';
            $docRetString = trim($docRetString);
            if ($docRetString == '') {
                $phpcsFile->addError(
                    "PHPDoc function return type missing",
                    $docBlockPtr,
                    'PHPDocFunRetTypeMissing'
                );
            }
        }

        return null;
    }

    /**
     * Simplify type to make comparison easier.
     *
     * @param string $type The type to be simplified
     * @return string|null
     */
    protected function simplifyType(string $type): ?string {
        if ($type == '') {
            return null;
        }

        do {
            // Simplify single-type arrays.
            $type = preg_replace(
                '/(?<=^|[(|&])[A-Za-z_\x7f-\xff0-9$\\\\]+\\[\\](?=$|[)|&\\[])/',
                'array',
                $type,
                -1,
                $count1
            );

            // Simplify multi-type arrays.
            $type = preg_replace(
                '/(?<=^|[(|&])\\([A-Za-z_\x7f-\xff0-9$\\\\]+(?:[|&][A-Za-z_\x7f-\xff0-9$\\\\]+)*\\)\\[\\](?=$|[)|&\\[])/',
                'array',
                $type,
                -1,
                $count2
            );
        } while ($count1 + $count2 > 0);

        // Remove brackets.
        $type = preg_replace(
            '/(?<=^|[|])\\(([A-Za-z_\x7f-\xff0-9$\\\\]+(?:[&][A-Za-z_\x7f-\xff0-9$\\\\]+)*)\\)(?=$|[|])/',
            '$1',
            $type
        );

        // Simplify nullable types.
        $type = preg_replace(
            '/^\\?([A-Za-z_\x7f-\xff0-9$\\\\]+)$/',
            '$1|null',
            $type
        );

        // Remove namespaces.
        $type = preg_replace('/[A-Za-z_\x7f-\xff0-9$]*\\\\/', '', $type);

        // Check type is well formed.
        if (!preg_match('/^[A-Za-z_\x7f-\xff0-9$\\\\]+(?:[|&][A-Za-z_\x7f-\xff0-9$\\\\]+)*$/', $type)) {
            $type = null;
        }

        return $type;
    }

    /**
     * Check if types match
     *
     * @param string $docTypeStr
     * @param string $nativeTypeStr
     * @return bool
     */
    protected function typeMatch(string $docTypeStr, string $nativeTypeStr): bool {
        $docTypeArray = array_unique(explode('|', $docTypeStr));
        $nativeTypeArray = array_unique(explode('|', $nativeTypeStr));

        // We don't need to check the Doc types that we can already see match.
        $docTypeArray = array_diff($docTypeArray, $nativeTypeArray);

        // We need to check every remaining Doc type.
        foreach ($docTypeArray as $docType) {
            $docParts = array_unique(explode('&', $docType));

            // The never type is a subtype of everything.
            if (in_array('never', $docParts)) {
                continue;
            }

            // Add super types.  This doesn't change the type,
            // But means we can match wider types.
            $docAdditions = [];
            foreach ($docParts as $docPart) {
                $docAdditions = array_merge(
                    $docAdditions,
                    ($this::TYPES[$docPart] ?? ['extends' => ['object']])['extends']
                );
            }
            $docParts = array_unique(array_merge($docParts, $docAdditions));
            if ($docParts != ['void']) {
                $docParts = array_merge($docParts, ['mixed']);
            }

            // Make sure there is a matching native type.
            $found = false;
            foreach ($nativeTypeArray as $nativeType) {
                $nativeParts = explode('&', $nativeType);
                if (count(array_diff($nativeParts, $docParts)) == 0) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                return false;
            }
        }

        return true;
    }
}
