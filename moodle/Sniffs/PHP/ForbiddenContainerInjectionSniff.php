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

namespace MoodleHQ\MoodleCS\moodle\Sniffs\PHP;

use MoodleHQ\MoodleCS\moodle\Util\NamespaceScopeUtil;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;
use PHPCSUtils\Tokens\Collections;
use PHPCSUtils\Utils\FunctionDeclarations;
use PHPCSUtils\Utils\Namespaces;
use PHPCSUtils\Utils\ObjectDeclarations;

/**
 * Prevents application code from receiving the dependency injection container.
 *
 * @copyright 2026 Cameron Ball <cameron@cameron1729.xyz>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class ForbiddenContainerInjectionSniff implements Sniff
{
    /**
     * Container types which application code must not receive.
     */
    private const FORBIDDEN_TYPES = [
        'core\di' => true,
        'di\container' => true,
        'psr\container\containerinterface' => true,
    ];

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
     * Register for all function-like declarations.
     *
     * @return array<int|string>
     */
    public function register() {
        return Collections::functionDeclarationTokens();
    }

    /**
     * Check declared parameter types for injected containers.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The function declaration token.
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr) {
        if ($this->isAllowedClass($phpcsFile, $stackPtr)) {
            return;
        }

        foreach (FunctionDeclarations::getParameters($phpcsFile, $stackPtr) as $parameter) {
            if ($parameter['type_hint'] === '') {
                continue;
            }

            $types = preg_split('/[?&|()\s]+/', $parameter['type_hint'], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($types as $type) {
                $qualifiedType = $this->getQualifiedTypeName($phpcsFile, $parameter['type_hint_token'], $type);
                if (!isset(self::FORBIDDEN_TYPES[strtolower($qualifiedType)])) {
                    continue;
                }

                $phpcsFile->addError(
                    'The container type %s must not be injected; declare the required service as a dependency instead.',
                    $parameter['type_hint_token'],
                    'ContainerInjected',
                    [$qualifiedType]
                );
                break;
            }
        }
    }

    /**
     * Whether the function belongs to approved container infrastructure.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The function declaration token.
     * @return bool
     */
    private function isAllowedClass(File $phpcsFile, int $stackPtr): bool {
        $tokens = $phpcsFile->getTokens();
        foreach (array_reverse($tokens[$stackPtr]['conditions'], true) as $conditionPtr => $conditionCode) {
            if (!isset(Tokens::$ooScopeTokens[$conditionCode])) {
                continue;
            }

            $className = ObjectDeclarations::getName($phpcsFile, $conditionPtr);
            if ($className === null) {
                return false;
            }

            $qualifiedClass = NamespaceScopeUtil::getQualifiedName($phpcsFile, $conditionPtr, $className);
            foreach ($this->allowedClasses as $allowedClass) {
                if (strcasecmp(ltrim($allowedClass, '\\'), $qualifiedClass) === 0) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Qualify a type relative to its namespace and imported class aliases.
     *
     * @param File $phpcsFile The file being scanned.
     * @param int $stackPtr The type declaration token.
     * @param string $type The declared type.
     * @return string
     */
    private function getQualifiedTypeName(File $phpcsFile, int $stackPtr, string $type): string {
        if (substr($type, 0, 1) === '\\') {
            return substr($type, 1);
        }

        if (strncasecmp($type, 'namespace\\', 10) === 0) {
            $namespace = Namespaces::determineNamespace($phpcsFile, $stackPtr);
            $relativeType = substr($type, 10);
            return $namespace === '' ? $relativeType : "{$namespace}\\{$relativeType}";
        }

        $separator = strpos($type, '\\');
        $alias = $separator === false ? $type : substr($type, 0, $separator);
        $remainder = $separator === false ? '' : substr($type, $separator);
        foreach (NamespaceScopeUtil::getClassImports($phpcsFile, $stackPtr) as $importAlias => $import) {
            if (strcasecmp($importAlias, $alias) === 0) {
                return ltrim($import, '\\') . $remainder;
            }
        }

        return NamespaceScopeUtil::getQualifiedName($phpcsFile, $stackPtr, $type);
    }
}
