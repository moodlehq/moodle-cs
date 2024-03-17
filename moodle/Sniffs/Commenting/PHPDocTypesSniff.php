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

/**
 * Check PHPDoc Types.
 *
 * @copyright  2024 Otago Polytechnic
 * @author     James Calder
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later (or CC BY-SA v4 or later)
 */

namespace MoodleHQ\MoodleCS\moodle\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use MoodleHQ\MoodleCS\moodle\Util\PHPDocTypeParser;

/**
 * Check PHPDoc Types.
 */
class PHPDocTypesSniff implements Sniff
{
    /** @var ?File the current file */
    protected ?File $file = null;

    /** @var array{'code': ?array-key, 'content': string, 'scope_opener'?: int, 'scope_closer'?: int}[]
     * file tokens */
    protected array $tokens = [];

    /** @var array<non-empty-string, object{extends: ?non-empty-string, implements: non-empty-string[]}>
     * classish things: classes, interfaces, traits, and enums */
    protected array $artifacts = [];

    /** @var ?PHPDocTypeParser */
    protected ?PHPDocTypeParser $typeparser = null;

    /** @var 1|2 pass 1 for gathering artifact/classish info, 2 for checking */
    protected int $pass = 1;

    /** @var int current token pointer in the file */
    protected int $fileptr = 0;

    /** @var non-empty-array<\stdClass&object{type: string, namespace: string, uses: string[], templates: string[],
     *                                  classname: ?string, parentname: ?string, opened: bool, closer: ?int}>
     * file scope: classish, function, etc.  We only need a closer if we might be in a switch statement. */
    protected array $scopes;

    /** @var ?(\stdClass&object{tags: array<string, string[]>}) PHPDoc comment for upcoming declaration */
    protected ?object $commentpending = null;

    /** @var int how long until we dispose of a pending comment */
    protected int $commentpendingcounter = 0;

    /** @var ?(\stdClass&object{tags: array<string, string[]>}) PHPDoc comment for current declaration */
    protected ?object $comment = null;

    /** @var array{'code': ?array-key, 'content': string, 'scope_opener'?: int, 'scope_closer'?: int}
     * the current token */
    protected array $token = ['code' => null, 'content' => ''];

    /** @var array{'code': ?array-key, 'content': string, 'scope_opener'?: int, 'scope_closer'?: int}
     * the previous token */
    protected array $tokenprevious = ['code' => null, 'content' => ''];

    /**
     * Register for open tag (only process once per file).
     * @return array-key[]
     */
    public function register(): array {
        return [T_OPEN_TAG];
    }

    /**
     * Processes PHP files and perform PHPDoc type checks with file.
     * @param File $phpcsfile The file being scanned.
     * @param int $stackptr The position in the stack.
     * @return void
     */
    public function process(File $phpcsfile, $stackptr): void {

        // Check we haven't already done this file.
        if ($phpcsfile == $this->file) {
            return;
        }

        try {
            $this->file = $phpcsfile;
            $this->tokens = $phpcsfile->getTokens();
            $this->artifacts = [];

            // Gather atifact info.
            $this->pass = 1;
            $this->typeparser = null;
            $this->fileptr = $stackptr;
            $this->processPass();

            // Check the PHPDoc types.
            $this->pass = 2;
            $this->typeparser = new PHPDocTypeParser($this->artifacts);
            $this->fileptr = $stackptr;
            $this->processPass();
        } catch (\Exception $e) {
            $this->file->addError(
                'The PHPDoc type sniff failed to parse the file.  PHPDoc type checks were not performed.',
                $this->fileptr < count($this->tokens) ? $this->fileptr : $this->fileptr - 1,
                'phpdoc_type_parse'
            );
        }
    }

    /**
     * A pass over the file.
     * @return void
     * @phpstan-impure
     */
    protected function processPass(): void {
        $this->scopes = [(object)['type' => 'root', 'namespace' => '', 'uses' => [], 'templates' => [],
                        'classname' => null, 'parentname' => null, 'opened' => true, 'closer' => null]];
        $this->tokenprevious = ['code' => null, 'content' => ''];
        $this->fetchToken();
        $this->commentpending = null;
        $this->comment = null;

        while ($this->token['code']) {
            // Skip irrelevant tokens.
            while (
                !in_array(
                    $this->token['code'],
                    [T_NAMESPACE, T_USE,
                    T_ABSTRACT, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_READONLY, T_FINAL,
                    T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT, T_ENUM,
                    T_FUNCTION, T_CLOSURE, T_VAR, T_CONST,
                    T_SEMICOLON, null]
                )
                && (!isset($this->token['scope_opener']) || $this->token['scope_opener'] != $this->fileptr)
                && (!isset($this->token['scope_closer']) || $this->token['scope_closer'] != $this->fileptr)
            ) {
                $this->advance();
            }

            // Check for the end of the file.
            if (!$this->token['code']) {
                break;
            }

            // Namespace.
            if ($this->token['code'] == T_NAMESPACE && end($this->scopes)->opened) {
                $this->processNamespace();
                continue;
            }

            // Use.
            if ($this->token['code'] == T_USE) {
                if (end($this->scopes)->type == 'classish' && end($this->scopes)->opened) {
                    $this->processClassTraitUse();
                } elseif (end($this->scopes)->type == 'function' && !end($this->scopes)->opened) {
                    $this->advance(T_USE);
                } else {
                    $this->processUse();
                }
                continue;
            }

            // Ignore constructor property promotion.  This has already been checked.
            if (
                end($this->scopes)->type == 'function' && !end($this->scopes)->opened
                && in_array($this->token['code'], [T_PUBLIC, T_PROTECTED, T_PRIVATE])
            ) {
                $this->advance();
                continue;
            }

            // Malformed prior declaration.
            if (
                !end($this->scopes)->opened
                    && !(isset($this->token['scope_opener']) && $this->token['scope_opener'] == $this->fileptr
                        || $this->token['code'] == T_SEMICOLON)
            ) {
                throw new \Exception();
            }

            // Opening a scope.
            if (isset($this->token['scope_opener']) && $this->token['scope_opener'] == $this->fileptr) {
                if ($this->token['scope_closer'] == end($this->scopes)->closer) {
                    // We're closing the previous scope at the same time.  This happens in switch statements.
                    if (count($this->scopes) <= 1) {
                        // Trying to close a scope that wasn't open.
                        throw new \Exception();
                    }
                    array_pop($this->scopes);
                }
                if (!end($this->scopes)->opened) {
                    end($this->scopes)->opened = true;
                } else {
                    $oldscope = end($this->scopes);
                    array_push($this->scopes, $newscope = clone $oldscope);
                    $newscope->type = 'other';
                    $newscope->opened = true;
                    $newscope->closer = $this->tokens[$this->fileptr]['scope_closer'];
                }
                $this->advance();
                continue;
            }

            // Closing a scope (without opening a new one).
            if (isset($this->token['scope_closer']) && $this->token['scope_closer'] == $this->fileptr) {
                if (count($this->scopes) <= 1) {
                    // Trying to close a scope that wasn't open.
                    throw new \Exception();
                }
                array_pop($this->scopes);
                $this->advance();
                continue;
            }

            // Empty declarations and other semicolons.
            if ($this->token['code'] == T_SEMICOLON) {
                if (!end($this->scopes)->opened) {
                    array_pop($this->scopes);
                }
                $this->advance(T_SEMICOLON);
                continue;
            }

            // Declarations.
            if (
                in_array(
                    $this->token['code'],
                    [T_ABSTRACT, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_READONLY, T_FINAL,
                    T_CLASS, T_ANON_CLASS, T_INTERFACE, T_TRAIT, T_ENUM,
                    T_FUNCTION, T_CLOSURE,
                    T_CONST, T_VAR, ]
                )
            ) {
                // Fetch comment.
                $this->comment = $this->commentpending;
                $this->commentpending = null;
                $static = false;
                $staticprecededbynew = ($this->tokenprevious['code'] == T_NEW);
                while (
                    in_array(
                        $this->token['code'],
                        [T_ABSTRACT, T_PUBLIC, T_PROTECTED, T_PRIVATE, T_STATIC, T_READONLY, T_FINAL]
                    )
                ) {
                    $static = ($this->token['code'] == T_STATIC);
                    $this->advance();
                }
                if ($static && ($this->token['code'] == T_DOUBLE_COLON || $staticprecededbynew)) {
                    // Ignore static late binding.
                } elseif (in_array($this->token['code'], [T_CLASS,  T_ANON_CLASS, T_INTERFACE, T_TRAIT, T_ENUM])) {
                    // Classish thing.
                    $this->processClassish();
                } elseif ($this->token['code'] == T_FUNCTION || $this->token['code'] == T_CLOSURE) {
                    // Function.
                    $this->processFunction();
                } else {
                    // Variable.
                    $this->processVariable();
                }
                $this->comment = null;
                continue;
            }

            // We got something unrecognised.
            throw new \Exception();
        }

        // Some scopes weren't closed.
        if (count($this->scopes) != 1) {
            throw new \Exception();
        }
    }

    /**
     * Fetch the current tokens.
     * @return void
     * @phpstan-impure
     */
    protected function fetchToken(): void {
        $this->token = ($this->fileptr < count($this->tokens)) ?
            $this->tokens[$this->fileptr]
            : ['code' => null, 'content' => ''];
    }

    /**
     * Advance the token pointer when reading PHP code.
     * @param array-key $expectedcode What we expect, or null if anything's OK
     * @return void
     * @phpstan-impure
     */
    protected function advance($expectedcode = null): void {

        // Check we have something to fetch, and it's what's expected.
        if ($expectedcode && $this->token['code'] != $expectedcode || $this->token['code'] == null) {
            throw new \Exception();
        }

        $nextptr = $this->fileptr + 1;

        // Skip stuff that doesn't effect us.
        while (
            $nextptr < count($this->tokens)
            && in_array($this->tokens[$nextptr]['code'], [T_WHITESPACE, T_COMMENT, T_INLINE_HTML, T_PHPCS_IGNORE])
        ) {
            $nextptr++;
        }

        $this->tokenprevious = $this->token;

        // Process PHPDoc comments.
        while ($nextptr < count($this->tokens) && $this->tokens[$nextptr]['code'] == T_DOC_COMMENT_OPEN_TAG) {
            $this->fileptr = $nextptr;
            $this->fetchToken();
            $this->processComment();
            $this->commentpendingcounter = 2;
            $nextptr = $this->fileptr;
        }

        $this->fileptr = $nextptr;
        $this->fetchToken();

        // Dispose of old comment.
        if ($this->commentpending) {
            $this->commentpendingcounter--;
            if ($this->commentpendingcounter <= 0) {
                $this->commentpending = null;
            }
        }
    }

    /**
     * Advance the token pointer when reading PHPDoc comments.
     * @param array-key $expectedcode What we expect, or null if anything's OK
     * @return void
     * @phpstan-impure
     */
    protected function advanceComment($expectedcode = null): void {

        // Check we are actually in a PHPDoc comment.
        if (
            !in_array(
                $this->token['code'],
                [T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_STAR,
                T_DOC_COMMENT_TAG, T_DOC_COMMENT_STRING, T_DOC_COMMENT_WHITESPACE,
                T_PHPCS_IGNORE]
            )
        ) {
            throw new \Exception();
        }

        // Check we have something to fetch, and it's what's expected.
        if ($expectedcode && $this->token['code'] != $expectedcode || $this->token['code'] == null) {
            throw new \Exception();
        }

        $this->fileptr++;

        // If we're expecting the end of the comment, then we need to advance to the next PHP code.
        if ($expectedcode == T_DOC_COMMENT_CLOSE_TAG) {
            while (
                $this->fileptr < count($this->tokens)
                && in_array($this->tokens[$this->fileptr]['code'], [T_WHITESPACE, T_COMMENT, T_INLINE_HTML])
            ) {
                $this->fileptr++;
            }
        }

        $this->fetchToken();
    }

    /**
     * Process a PHPDoc comment.
     * @return void
     * @phpstan-impure
     */
    protected function processComment(): void {
        $this->commentpending = (object)['tags' => []];

        // Skip line starting stuff.
        while (
            in_array($this->token['code'], [T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_STAR])
                || $this->token['code'] == T_DOC_COMMENT_WHITESPACE
                    && !in_array(substr($this->token['content'], -1), ["\n", "\r"])
        ) {
            $this->advanceComment();
        }

        // For each tag.
        while ($this->token['code'] != T_DOC_COMMENT_CLOSE_TAG) {
            // Fetch the tag type.
            if ($this->token['code'] == T_DOC_COMMENT_TAG) {
                $tagtype = $this->token['content'];
                $this->advanceComment(T_DOC_COMMENT_TAG);
            } else {
                $tagtype = '';
            }
            $tagcontent = '';

            // For each line, until we reach a new tag.
            do {
                $newline = false;
                // Fetch line content.
                while ($this->token['code'] != T_DOC_COMMENT_CLOSE_TAG && !$newline) {
                    $tagcontent .= $this->token['content'];
                    $newline = in_array(substr($this->token['content'], -1), ["\n", "\r"]);
                    $this->advanceComment();
                }
                // Skip next line starting stuff.
                while (
                    in_array($this->token['code'], [T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_STAR])
                        || $this->token['code'] == T_DOC_COMMENT_WHITESPACE
                            && !in_array(substr($this->token['content'], -1), ["\n", "\r"])
                ) {
                    $this->advanceComment();
                }
            } while (!in_array($this->token['code'], [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_TAG]));

            // Store tag content.
            if (!isset($this->commentpending->tags[$tagtype])) {
                $this->commentpending->tags[$tagtype] = [];
            }
            $this->commentpending->tags[$tagtype][] = trim($tagcontent);
        }
        $this->advanceComment(T_DOC_COMMENT_CLOSE_TAG);
    }

    /**
     * Process a namespace declaration.
     * @return void
     * @phpstan-impure
     */
    protected function processNamespace(): void {
        $this->advance(T_NAMESPACE);
        $namespace = '';
        while (
            in_array(
                $this->token['code'],
                [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_NS_SEPARATOR, T_STRING]
            )
        ) {
            $namespace .= $this->token['content'];
            $this->advance();
        }
        if ($namespace != '' && $namespace[strlen($namespace) - 1] == "\\") {
            throw new \Exception();
        }
        if ($namespace != '' && $namespace[0] != "\\") {
            $namespace = "\\" . $namespace;
        }
        if (!in_array($this->token['code'], [T_OPEN_CURLY_BRACKET, T_SEMICOLON])) {
            throw new \Exception();
        }
        if ($this->token['code'] == T_SEMICOLON) {
            end($this->scopes)->namespace = $namespace;
        } else {
            $oldscope = end($this->scopes);
            array_push($this->scopes, $newscope = clone $oldscope);
            $newscope->type = 'namespace';
            $newscope->namespace = $namespace;
            $newscope->opened = false;
            $newscope->closer = null;
        }
    }

    /**
     * Process a use declaration.
     * @return void
     * @phpstan-impure
     */
    protected function processUse(): void {
        $this->advance(T_USE);
        $more = false;
        do {
            $namespace = '';
            $type = 'class';
            if ($this->token['code'] == T_FUNCTION) {
                $type = 'function';
                $this->advance(T_FUNCTION);
            } elseif ($this->token['code'] == T_CONST) {
                $type = 'const';
                $this->advance(T_CONST);
            }
            while (
                in_array(
                    $this->token['code'],
                    [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_NS_SEPARATOR, T_STRING]
                )
            ) {
                $namespace .= $this->token['content'];
                $this->advance();
            }
            if ($namespace != '' && $namespace[0] != "\\") {
                $namespace = "\\" . $namespace;
            }
            if ($this->token['code'] == T_OPEN_USE_GROUP) {
                $namespacestart = $namespace;
                if ($namespacestart && strrpos($namespacestart, "\\") != strlen($namespacestart) - 1) {
                    throw new \Exception();
                }
                $typestart = $type;
                $this->advance(T_OPEN_USE_GROUP);
                do {
                    $namespaceend = '';
                    $type = $typestart;
                    if ($this->token['code'] == T_FUNCTION) {
                        $type = 'function';
                        $this->advance(T_FUNCTION);
                    } elseif ($this->token['code'] == T_CONST) {
                        $type = 'const';
                        $this->advance(T_CONST);
                    }
                    while (
                        in_array(
                            $this->token['code'],
                            [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_NS_SEPARATOR, T_STRING]
                        )
                    ) {
                        $namespaceend .= $this->token['content'];
                        $this->advance();
                    }
                    $namespace = $namespacestart . $namespaceend;
                    $alias = substr($namespace, strrpos($namespace, "\\") + 1);
                    $asalias = $this->processUseAsAlias();
                    $alias = $asalias ?? $alias;
                    if ($this->pass == 2 && $type == 'class') {
                        end($this->scopes)->uses[$alias] = $namespace;
                    }
                    $more = ($this->token['code'] == T_COMMA);
                    if ($more) {
                        $this->advance(T_COMMA);
                    }
                } while ($more);
                $this->advance(T_CLOSE_USE_GROUP);
            } else {
                $alias = (strrpos($namespace, "\\") !== false) ?
                    substr($namespace, strrpos($namespace, "\\") + 1)
                    : $namespace;
                if ($alias == '') {
                    throw new \Exception();
                }
                $asalias = $this->processUseAsAlias();
                $alias = $asalias ?? $alias;
                if ($this->pass == 2 && $type == 'class') {
                    end($this->scopes)->uses[$alias] = $namespace;
                }
            }
            $more = ($this->token['code'] == T_COMMA);
            if ($more) {
                $this->advance(T_COMMA);
            }
        } while ($more);
        if ($this->token['code'] != T_SEMICOLON) {
            throw new \Exception();
        }
    }

    /**
     * Process a use as alias.
     * @return ?string
     * @phpstan-impure
     */
    protected function processUseAsAlias(): ?string {
        $alias = null;
        if ($this->token['code'] == T_AS) {
            $this->advance(T_AS);
            if ($this->token['code'] == T_STRING) {
                $alias = $this->token['content'];
                $this->advance(T_STRING);
            }
        }
        return $alias;
    }

    /**
     * Process a classish thing.
     * @return void
     * @phpstan-impure
     */
    protected function processClassish(): void {

        // Get details.
        $name = $this->file->getDeclarationName($this->fileptr);
        $name = $name ? end($this->scopes)->namespace . "\\" . $name : null;
        $parent = $this->file->findExtendedClassName($this->fileptr);
        if ($parent && $parent[0] != "\\") {
            $parent = end($this->scopes)->namespace . "\\" . $parent;
        }
        $interfaces = $this->file->findImplementedInterfaceNames($this->fileptr);
        if (!is_array($interfaces)) {
            $interfaces = [];
        }
        foreach ($interfaces as $index => $interface) {
            if ($interface && $interface[0] != "\\") {
                $interfaces[$index] = end($this->scopes)->namespace . "\\" . $interface;
            }
        }

        // Add to scopes.
        $oldscope = end($this->scopes);
        array_push($this->scopes, $newscope = clone $oldscope);
        $newscope->type = 'classish';
        $newscope->classname = $name;
        $newscope->parentname = $parent;
        $newscope->opened = false;
        $newscope->closer = null;

        if ($this->pass == 1 && $name) {
            // Store details.
            $this->artifacts[$name] = (object)['extends' => $parent, 'implements' => $interfaces];
        } elseif ($this->pass == 2) {
            // Check and store templates.
            if ($this->comment && isset($this->comment->tags['@template'])) {
                $this->processTemplates();
            }
        }

        $this->advance();
    }

    /**
     * Process a class trait usage.
     * @return void
     * @phpstan-impure
     */
    protected function processClassTraitUse(): void {
        $this->advance(T_USE);

        while (
            in_array(
                $this->token['code'],
                [T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_NS_SEPARATOR, T_STRING]
            )
        ) {
            $this->advance();
        }

        if ($this->token['code'] == T_OPEN_CURLY_BRACKET) {
            $this->advance(T_OPEN_CURLY_BRACKET);
            do {
                $this->advance(T_STRING);
                if ($this->token['code'] == T_AS) {
                    $this->advance(T_AS);
                    while (in_array($this->token['code'], [T_PUBLIC, T_PROTECTED, T_PRIVATE])) {
                        $this->advance();
                    }
                    if ($this->token['code'] == T_STRING) {
                        $this->advance(T_STRING);
                    }
                }
                if ($this->token['code'] == T_SEMICOLON) {
                    $this->advance(T_SEMICOLON);
                }
            } while ($this->token['code'] != T_CLOSE_CURLY_BRACKET);
            $this->advance(T_CLOSE_CURLY_BRACKET);
        }
    }

    /**
     * Process a function.
     * @return void
     * @phpstan-impure
     */
    protected function processFunction(): void {

        // Get details.
        $name = $this->file->getDeclarationName($this->fileptr);
        $parameters = $this->file->getMethodParameters($this->fileptr);
        $properties = $this->file->getMethodProperties($this->fileptr);

        // Push to scopes.
        $oldscope = end($this->scopes);
        array_push($this->scopes, $newscope = clone $oldscope);
        $newscope->type = 'function';
        $newscope->opened = false;
        $newscope->closer = null;

        // Checks.
        if ($this->pass == 2) {
            // Check for missing docs if not anonymous.
            if ($name && !$this->comment) {
                $this->file->addWarning(
                    'PHPDoc function is not documented',
                    $this->fileptr,
                    'phpdoc_fun_doc_missing'
                );
            }

            // Check and store templates.
            if ($this->comment && isset($this->comment->tags['@template'])) {
                $this->processTemplates();
            }

            // Check parameter types.
            if ($this->comment && isset($parameters)) {
                if (!isset($this->comment->tags['@param'])) {
                    $this->comment->tags['@param'] = [];
                }
                if (count($this->comment->tags['@param']) != count($parameters)) {
                    $this->file->addError(
                        "PHPDoc number of function @param tags doesn't match actual number of parameters",
                        $this->fileptr,
                        'phpdoc_fun_param_count'
                    );
                }
                for ($varnum = 0; $varnum < count($this->comment->tags['@param']); $varnum++) {
                    $docparamdata = $this->typeparser->parseTypeAndVar(
                        $newscope,
                        $this->comment->tags['@param'][$varnum],
                        2,
                        false
                    );
                    if (!$docparamdata->type) {
                        $this->file->addError(
                            'PHPDoc function parameter %s type missing or malformed',
                            $this->fileptr,
                            'phpdoc_fun_param_type',
                            [$varnum + 1]
                        );
                    } elseif (!$docparamdata->var) {
                        $this->file->addError(
                            'PHPDoc function parameter %s name missing or malformed',
                            $this->fileptr,
                            'phpdoc_fun_param_name',
                            [$varnum + 1]
                        );
                    } elseif ($varnum < count($parameters)) {
                        $paramdata = $this->typeparser->parseTypeAndVar(
                            $newscope,
                            $parameters[$varnum]['content'],
                            3,
                            true
                        );
                        if (!$this->typeparser->comparetypes($paramdata->type, $docparamdata->type)) {
                            $this->file->addError(
                                'PHPDoc function parameter %s type mismatch',
                                $this->fileptr,
                                'phpdoc_fun_param_type_mismatch',
                                [$varnum + 1]
                            );
                        }
                        if ($paramdata->passsplat != $docparamdata->passsplat) {
                            $this->file->addWarning(
                                'PHPDoc function parameter %s splat mismatch',
                                $this->fileptr,
                                'phpdoc_fun_param_pass_splat_mismatch',
                                [$varnum + 1]
                            );
                        }
                        if ($paramdata->var != $docparamdata->var) {
                            $this->file->addError(
                                'PHPDoc function parameter %s name mismatch',
                                $this->fileptr,
                                'phpdoc_fun_param_name_mismatch',
                                [$varnum + 1]
                            );
                        }
                    }
                }
            }

            // Check return type.
            if ($this->comment && isset($properties)) {
                if (!isset($this->comment->tags['@return'])) {
                    $this->comment->tags['@return'] = [];
                }
                // The old checker didn't check this.
                /*if (count($this->comment->tags['@return']) < 1 && $name != '__construct') {
                    $this->file->addError(
                        'PHPDoc missing function @return tag',
                        $this->fileptr,
                        'phpdoc_fun_ret_missing'
                    );
                } else*/
                if (count($this->comment->tags['@return']) > 1) {
                    $this->file->addError(
                        'PHPDoc multiple function @return tags--Put in one tag, seperated by vertical bars |',
                        $this->fileptr,
                        'phpdoc_fun_ret_multiple'
                    );
                }
                $retdata = $properties['return_type'] ?
                    $this->typeparser->parseTypeAndVar(
                        $newscope,
                        $properties['return_type'],
                        0,
                        true
                    )
                    : (object)['type' => 'mixed'];
                for ($retnum = 0; $retnum < count($this->comment->tags['@return']); $retnum++) {
                    $docretdata = $this->typeparser->parseTypeAndVar(
                        $newscope,
                        $this->comment->tags['@return'][$retnum],
                        0,
                        false
                    );
                    if (!$docretdata->type) {
                        $this->file->addError(
                            'PHPDoc function return type missing or malformed',
                            $this->fileptr,
                            'phpdoc_fun_ret_type'
                        );
                    } elseif (!$this->typeparser->comparetypes($retdata->type, $docretdata->type)) {
                        $this->file->addError(
                            'PHPDoc function return type mismatch',
                            $this->fileptr,
                            'phpdoc_fun_ret_type_mismatch'
                        );
                    }
                }
            }
        }

        $this->advance();
        if ($this->token['code'] == T_BITWISE_AND) {
            $this->advance(T_BITWISE_AND);
        }

        // Function name.
        if ($this->token['code'] == T_STRING) {
            $this->advance(T_STRING);
        }

        // Parameters.
        if ($this->token['code'] != T_OPEN_PARENTHESIS) {
            throw new \Exception();
        }
    }

    /**
     * Process templates.
     * @return void
     * @phpstan-impure
     */
    protected function processTemplates(): void {
        $newscope = end($this->scopes);
        foreach ($this->comment->tags['@template'] as $templatetext) {
            $templatedata = $this->typeparser->parseTemplate($newscope, $templatetext);
            if (!$templatedata->var) {
                $this->file->addError('PHPDoc template name missing or malformed', $this->fileptr, 'phpdoc_template_name');
            } elseif (!$templatedata->type) {
                $this->file->addError('PHPDoc template type missing or malformed', $this->fileptr, 'phpdoc_template_type');
                $newscope->templates[$templatedata->var] = 'never';
            } else {
                $newscope->templates[$templatedata->var] = $templatedata->type;
            }
        }
    }

    /**
     * Process a variable.
     * @return void
     * @phpstan-impure
     */
    protected function processVariable(): void {

        // Parse var/const token.
        $const = ($this->token['code'] == T_CONST);
        if ($const) {
            $this->advance(T_CONST);
        } elseif ($this->token['code'] == T_VAR) {
            $this->advance(T_VAR);
        }

        // Parse type.
        if (!$const) {
            while (
                in_array(
                    $this->token['code'],
                    [T_TYPE_UNION, T_TYPE_INTERSECTION, T_NULLABLE, T_OPEN_PARENTHESIS, T_CLOSE_PARENTHESIS,
                    T_NAME_FULLY_QUALIFIED, T_NAME_QUALIFIED, T_NAME_RELATIVE, T_NS_SEPARATOR, T_STRING,
                    T_NULL, T_ARRAY, T_OBJECT, T_SELF, T_PARENT, T_FALSE, T_TRUE, T_CALLABLE, T_STATIC, ]
                )
            ) {
                $this->advance();
            }
        }

        // Check name.
        if ($this->token['code'] != ($const ? T_STRING : T_VARIABLE)) {
            throw new \Exception();
        }

        // Checking.
        if ($this->pass == 2) {
            // Get properties, unless it's a function static variable or constant.
            $properties = (end($this->scopes)->type == 'classish' && !$const) ?
                $this->file->getMemberProperties($this->fileptr)
                : null;

            if (!$this->comment && end($this->scopes)->type == 'classish') {
                // Require comments for class variables and constants.
                $this->file->addWarning(
                    'PHPDoc variable or constant is not documented',
                    $this->fileptr,
                    'phpdoc_var_doc_missing'
                );
            } elseif ($this->comment) {
                if (!isset($this->comment->tags['@var'])) {
                    $this->comment->tags['@var'] = [];
                }
                if (count($this->comment->tags['@var']) < 1) {
                    $this->file->addError('PHPDoc missing @var tag', $this->fileptr, 'phpdoc_var_missing');
                } elseif (count($this->comment->tags['@var']) > 1) {
                    $this->file->addError('PHPDoc multiple @var tags', $this->fileptr, 'phpdoc_var_multiple');
                }
                $vardata = ($properties && $properties['type']) ?
                    $this->typeparser->parseTypeAndVar(
                        end($this->scopes),
                        $properties['type'],
                        0,
                        true
                    )
                    : (object)['type' => 'mixed'];
                for ($varnum = 0; $varnum < count($this->comment->tags['@var']); $varnum++) {
                    $docvardata = $this->typeparser->parseTypeAndVar(
                        end($this->scopes),
                        $this->comment->tags['@var'][$varnum],
                        0,
                        false
                    );
                    if (!$docvardata->type) {
                        $this->file->addError(
                            'PHPDoc var type missing or malformed',
                            $this->fileptr,
                            'phpdoc_var_type',
                            [$varnum + 1]
                        );
                    } elseif (!$this->typeparser->comparetypes($vardata->type, $docvardata->type)) {
                        $this->file->addError(
                            'PHPDoc var type mismatch',
                            $this->fileptr,
                            'phpdoc_var_type_mismatch'
                        );
                    }
                }
            }
        }

        $this->advance();

        if (!in_array($this->token['code'], [T_EQUAL, T_COMMA, T_SEMICOLON])) {
            throw new \Exception();
        }
    }
}
