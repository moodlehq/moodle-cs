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

/**
 * Checks that each file contains necessary login checks.
 *
 * @package    local_codechecker
 * @copyright  2022 Laurent David <laurent.david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace MoodleCodeSniffer\moodle\Sniffs\Access;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DeprecatedCapabilitySniff implements Sniff {

    // phpcs:disable moodle.NamingConventions.ValidVariableName.MemberNameUnderscore
    /**
     * If we try to check this capability, a warning will be shown.
     *
     * @var array
     */
    public $capabilitiesWarningList = [];

    /**
     * If we try to check this capability, an error will be shown.
     *
     * @var array
     */
    public $capabilitiesErrorList = [];

    // phpcs:enable

    /**
     * Access function type
     *
     * @var string[]
     */
    public $accessfunctions = ['has_access', 'has_capability'];

    /**
     * Register for open tag (only process once per file).
     */
    public function register() {
        return array(T_OPEN_PARENTHESIS);
    }

    /**
     * Processes php files and for required login checks if includeing config.php
     *
     * @param File $file The file being scanned.
     * @param int $stackptr The position in the stack.
     */
    public function process(File $file, $stackptr) {
        $functionnamepos = $file->findPrevious(T_STRING, $stackptr - 1);
        if ($functionnamepos === false) {
            return;
        }
        $tokens = $file->getTokens();
        if (empty($tokens[$functionnamepos]) || !$this->is_an_access_function($tokens[$functionnamepos])) {
            return;
        }

        $alldeprecated = array_merge($this->capabilitiesErrorList, $this->capabilitiesWarningList);
        $closeparenthesispos = $file->findNext(T_CLOSE_PARENTHESIS, $stackptr + 1);
        if ($closeparenthesispos === false) {
            // Live coding, parse error or not a function call.
            return;
        }
        $closeparenthesistoken = $tokens[$closeparenthesispos];
        $starttoken = $closeparenthesistoken['parenthesis_opener'] + 1;
        $endtoken = $closeparenthesistoken['parenthesis_closer'];
        $values = $file->getTokensAsString($starttoken, $endtoken - $starttoken);
        foreach ($alldeprecated as $capability) {
            if (strpos($values, $capability) !== false) {
                if (in_array($capability, $this->capabilitiesWarningList)) {
                    $file->addWarning("The following capability '$capability' will be deprecated soon.", $starttoken,
                        'DeprecatedCapability');
                } else {
                    $file->addError("The following capability '$capability' has been deprecated.", $starttoken,
                        'DeprecatedCapability');
                }
            }
        }
    }

    /**
     * Is the current name an access function
     *
     * @param array $token current token
     * @return bool true if the current methodname is access function.
     */
    protected function is_an_access_function(array $token) {
        return in_array($token['content'], $this->accessfunctions);
    }
}
