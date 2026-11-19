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

namespace MoodleHQ\MoodleCS\moodle\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;
use PHPCSUtils\Internal\Cache;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\Parentheses;
use PHPCSUtils\Utils\UseStatements;

/**
 * Suggest core_text alternatives for PHP string functions.
 *
 * @copyright 2026 Cameron Ball <cameron@cameron1729.xyz>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class PreferCoreTextSniff extends ForbiddenFunctionsSniff
{
    /** @var array PHP string functions and their core_text alternatives. */
    public $forbiddenFunctions = [
        'substr' => 'core_text::substr',
        'strlen' => 'core_text::strlen',
        'strtolower' => 'core_text::strtolower',
        'strtoupper' => 'core_text::strtoupper',
        'strpos' => 'core_text::strpos',
        'strrpos' => 'core_text::strrpos',
        'strrchr' => 'core_text::strrchr',
        'strrev' => 'core_text::strrev',
    ];

    /**
     * Suggest the core_text equivalent.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the function call.
     * @param string $function The function name.
     * @param string|null $pattern The matched function pattern, if any.
     */
    protected function addError($phpcsFile, $stackPtr, $function, $pattern = null): void {
        $opener = $phpcsFile->findNext(T_OPEN_PARENTHESIS, $stackPtr + 1);
        if (Parentheses::isOwnerIn($phpcsFile, $opener, T_FUNCTION)) {
            return;
        }

        // Skip calls such as namespace\substr() in PHPCS 3.
        $tokens = $phpcsFile->getTokens();
        if (
            ($tokens[$stackPtr - 1]['code'] ?? null) === T_NS_SEPARATOR &&
            ($tokens[$stackPtr - 2]['code'] ?? null) === T_NAMESPACE
        ) {
            return;
        }

        $function = $pattern ?? strtolower($function);
        if (
            $tokens[$stackPtr]['code'] !== T_NAME_FULLY_QUALIFIED &&
            ($tokens[$stackPtr - 1]['code'] ?? null) !== T_NS_SEPARATOR
        ) {
            $function = $this->getImportedFunction($phpcsFile, $stackPtr, $function);
        }
        if (!isset($this->forbiddenFunctions[$function])) {
            return;
        }

        $phpcsFile->addWarning(
            'Consider %s().',
            $stackPtr,
            ucfirst($function),
            [$this->forbiddenFunctions[$function]]
        );
    }

    /**
     * Resolve a function import.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the function call.
     * @param string $function The function name.
     * @return string The imported or original function name.
     */
    private function getImportedFunction(File $phpcsFile, int $stackPtr, string $function): string {
        $namespace = Namespaces::findNamespacePtr($phpcsFile, $stackPtr);
        $cacheKey = $namespace === false ? 'global' : $namespace;
        if (!Cache::isCached($phpcsFile, __METHOD__, $cacheKey)) {
            $imports = [];
            $start = $namespace === false ? 0 : $namespace + 1;
            for (
                $use = $phpcsFile->findNext(T_USE, $start);
                $use !== false;
                $use = $phpcsFile->findNext(T_USE, $use + 1)
            ) {
                if (
                    !UseStatements::isImportUse($phpcsFile, $use) ||
                    Namespaces::findNamespacePtr($phpcsFile, $use) !== $namespace
                ) {
                    continue;
                }

                foreach (UseStatements::splitImportUseStatement($phpcsFile, $use)['function'] as $alias => $name) {
                    $imports[strtolower($alias)] = ['name' => strtolower($name), 'position' => $use];
                }
            }
            Cache::set($phpcsFile, __METHOD__, $cacheKey, $imports);
        }

        $imports = Cache::get($phpcsFile, __METHOD__, $cacheKey);
        if (isset($imports[$function]) && $imports[$function]['position'] < $stackPtr) {
            return $imports[$function]['name'];
        }
        return $function;
    }
}
