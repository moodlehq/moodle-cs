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

use PHP_CodeSniffer\Files\File;
use PHPCSUtils\Utils\ObjectDeclarations;

class TokenUtil
{
    /**
     * Get the human-readable object type.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return string
     */
    public static function getObjectType(
        File $phpcsFile,
        int $stackPtr
    ): string {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['code'] === T_OPEN_TAG) {
            return 'file';
        }
        return $tokens[$stackPtr]['content'];
    }

    /**
     * Get the human readable object name.
     *
     * @param File $phpcsFile
     * @param int $stackPtr
     * @return string
     */
    public static function getObjectName(
        File $phpcsFile,
        int $stackPtr
    ): string {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['code'] === T_OPEN_TAG) {
            return basename($phpcsFile->getFilename());
        }

        return ObjectDeclarations::getName($phpcsFile, $stackPtr);
    }
}
