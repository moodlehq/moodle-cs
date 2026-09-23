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

namespace MoodleHQ\MoodleCS\MoodleExtra\Sniffs\PHP;

use MoodleHQ\MoodleCS\moodle\Util\MoodleUtil;
use MoodleHQ\MoodleCS\moodle\Util\NamespaceScopeUtil;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\GetTokensAsString;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;

/**
 * Warns when a class accesses the dependency injection container directly.
 *
 * @copyright 2026 Cameron Ball <cameron@cameron1729.xyz>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class DiscouragedContainerLookupSniff implements Sniff
{
    /**
     * Whether to ignore files in test directories.
     */
    public bool $ignoreTestFiles = true;

    /**
     * Core classes which implement the container or its framework integration.
     *
     * @var string[]
     */
    public array $allowedClasses = [
        'core\di',
        'core\router\bridge',
        'core\router\controller_invoker',
        'core\router\response_handler',
    ];

    /**
     * Register for possible static method names.
     *
     * @return array<int|string>
     */
    public function register() {
        return [T_STRING];
    }

    /**
     * Check for direct container lookups inside object scopes.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The string token being inspected.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr) {
        $tokens = $phpcsFile->getTokens();
        $method = strtolower($tokens[$stackPtr]['content']);
        if (!in_array($method, ['get', 'get_container'], true)) {
            return;
        }

        if ($this->ignoreTestFiles && $this->isTestFile($phpcsFile)) {
            return;
        }

        $objectPtr = $this->getEnclosingObjectScope($tokens[$stackPtr]['conditions']);
        if ($objectPtr === null) {
            return;
        }

        $doubleColon = $phpcsFile->findPrevious(Tokens::$emptyTokens, $stackPtr - 1, null, true);
        if ($doubleColon === false || $tokens[$doubleColon]['code'] !== T_DOUBLE_COLON) {
            return;
        }

        $openParenthesis = $phpcsFile->findNext(Tokens::$emptyTokens, $stackPtr + 1, null, true);
        if ($openParenthesis === false || $tokens[$openParenthesis]['code'] !== T_OPEN_PARENTHESIS) {
            return;
        }

        $className = $this->getStaticClassName($phpcsFile, $doubleColon);
        if ($className === null) {
            return;
        }

        $qualifiedClass = $this->getQualifiedClassName($phpcsFile, $stackPtr, $className);
        if (strcasecmp($qualifiedClass, 'core\di') !== 0 || $this->isAllowedClass($phpcsFile, $objectPtr)) {
            return;
        }

        $message = 'Calling %s::%s() inside a class is service location unless this method is a composition root; ' .
            'inject the required service or suppress this warning at an intentional boundary.';
        $phpcsFile->addWarning(
            $message,
            $stackPtr,
            'InClass',
            [$qualifiedClass, $method]
        );
    }

    /**
     * Whether this is a file in a test directory.
     *
     * @param File $phpcsFile The file being scanned.
     * @return bool
     */
    private function isTestFile(File $phpcsFile): bool {
        $filename = MoodleUtil::getStandardisedFilename($phpcsFile);
        return stripos($filename, '/tests/') !== false;
    }

    /**
     * Get the innermost enclosing object scope.
     *
     * @param array<int, int|string> $conditions The token's enclosing conditions.
     * @return int|null The object declaration token, or null when outside an object scope.
     */
    private function getEnclosingObjectScope(array $conditions): ?int {
        foreach (array_reverse($conditions, true) as $conditionPtr => $conditionCode) {
            if (isset(Tokens::$ooScopeTokens[$conditionCode])) {
                return $conditionPtr;
            }
        }

        return null;
    }

    /**
     * Find the class name to the left of a static access operator.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $doubleColon The static access operator token.
     * @return string|null
     */
    private function getStaticClassName(File $phpcsFile, int $doubleColon): ?string {
        $tokens = $phpcsFile->getTokens();
        $classEnd = $phpcsFile->findPrevious(Tokens::$emptyTokens, $doubleColon - 1, null, true);
        if ($classEnd === false || !isset(Collections::namespacedNameTokens()[$tokens[$classEnd]['code']])) {
            return null;
        }

        $classStart = $classEnd;
        while ($classStart > 0) {
            $previous = $phpcsFile->findPrevious(Tokens::$emptyTokens, $classStart - 1, null, true);
            if ($previous === false || !isset(Collections::namespacedNameTokens()[$tokens[$previous]['code']])) {
                break;
            }
            $classStart = $previous;
        }

        return GetTokensAsString::noEmpties($phpcsFile, $classStart, $classEnd);
    }

    /**
     * Qualify a class name relative to its namespace and imported aliases.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The static method name token.
     * @param string $className The declared class name.
     * @return string
     */
    private function getQualifiedClassName(File $phpcsFile, int $stackPtr, string $className): string {
        if (substr($className, 0, 1) === '\\') {
            return substr($className, 1);
        }

        if (strncasecmp($className, 'namespace\\', 10) === 0) {
            $namespace = Namespaces::determineNamespace($phpcsFile, $stackPtr);
            $relativeClass = substr($className, 10);
            return $namespace === '' ? $relativeClass : "{$namespace}\\{$relativeClass}";
        }

        $separator = strpos($className, '\\');
        $alias = $separator === false ? $className : substr($className, 0, $separator);
        $remainder = $separator === false ? '' : substr($className, $separator);
        foreach (NamespaceScopeUtil::getClassImports($phpcsFile, $stackPtr) as $importAlias => $import) {
            if (strcasecmp($importAlias, $alias) === 0) {
                return ltrim($import, '\\') . $remainder;
            }
        }

        return NamespaceScopeUtil::getQualifiedName($phpcsFile, $stackPtr, $className);
    }

    /**
     * Whether the call belongs to approved container infrastructure.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $objectPtr The object declaration token.
     * @return bool
     */
    private function isAllowedClass(File $phpcsFile, int $objectPtr): bool {
        $className = ObjectDeclarations::getName($phpcsFile, $objectPtr);
        if ($className === null) {
            return false;
        }

        $qualifiedClass = NamespaceScopeUtil::getQualifiedName($phpcsFile, $objectPtr, $className);
        foreach ($this->allowedClasses as $allowedClass) {
            if (strcasecmp(ltrim($allowedClass, '\\'), $qualifiedClass) === 0) {
                return true;
            }
        }

        return false;
    }
}
