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

declare(strict_types=1);

namespace MoodleHQ\MoodleCS\moodle\Sniffs\Commenting;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Util\Tokens;
use MoodleHQ\MoodleCS\moodle\Util\PHPDocTypeParser;

/**
 * Check PHPDoc Types.
 */
class PHPDocTypesSniff implements Sniff
{
    /** @var ?File the current file */
    protected ?File $file = null;

    /** @var array{'code': ?array-key, 'content': string, 'scope_opener'?: int, 'scope_closer'?: int,
     *              'parenthesis_opener'?: int, 'parenthesis_closer'?: int, 'attribute_closer'?: int}[]
     * file tokens */
    protected array $tokens = [];

    /** @var array<non-empty-string, object{extends: ?non-empty-string, implements: non-empty-string[]}>
     * classish things: classes, interfaces, traits, and enums */
    protected array $artifacts = [];

    /** @var ?PHPDocTypeParser for parsing and comparing types */
    protected ?PHPDocTypeParser $typeparser = null;

    /** @var 1|2 pass 1 for gathering artifact/classish info, 2 for checking */
    protected int $pass = 1;

    /** @var int current token pointer in the file */
    protected int $fileptr = 0;

    /** @var ?(\stdClass&object{ptr: int, tags: array<string, object{ptr: int, content: string, cstartptr: ?int, cendptr: ?int}[]>})
     * PHPDoc comment for upcoming declaration */
    protected ?object $commentpending = null;

    /** @var int how long until we dispose of a pending comment */
    protected int $commentpendingcounter = 0;

    /** @var ?(\stdClass&object{ptr: int, tags: array<string, object{ptr: int, content: string, cstartptr: ?int, cendptr: ?int}[]>})
     * PHPDoc comment for current declaration */
    protected ?object $comment = null;

    /** @var array{'code': ?array-key, 'content': string, 'scope_opener'?: int, 'scope_closer'?: int,
     *              'parenthesis_opener'?: int, 'parenthesis_closer'?: int, 'attribute_closer'?: int}
     * the current token */
    protected array $token = ['code' => null, 'content' => ''];

    /** @var array{'code': ?array-key, 'content': string, 'scope_opener'?: int, 'scope_closer'?: int,
     *              'parenthesis_opener'?: int, 'parenthesis_closer'?: int, 'attribute_closer'?: int}
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

        try {
            $this->file = $phpcsfile;
            $this->tokens = $phpcsfile->getTokens();

            // Check we haven't already seen this file.
            for ($tagcounter = $stackptr - 1; $tagcounter >= 0; $tagcounter--) {
                if ($this->tokens[$tagcounter]['code'] == T_OPEN_TAG) {
                    return;
                }
            }

            // Gather atifact info.
            $this->artifacts = [];
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
            // Give up.  The user will probably want to fix parse errors before anything else.
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
        $scope = (object)[
            'namespace' => '', 'uses' => [], 'templates' => [], 'closer' => null,
            'classname' => null, 'parentname' => null, 'type' => 'root',
        ];
        $this->tokenprevious = ['code' => null, 'content' => ''];
        $this->fetchToken();
        $this->commentpending = null;
        $this->comment = null;

        $this->processBlock($scope);
    }

    /**
     * Process the content of a file, class, or function
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processBlock(object $scope): void {

        // Check we are at the start of a scope.
        if (!($this->token['code'] == T_OPEN_TAG || $this->token['scope_opener'] == $this->fileptr)) {
            throw new \Exception();
        }

        $scope->closer = ($this->token['code'] == T_OPEN_TAG) ?
            count($this->tokens)
            : $this->token['scope_closer'];
        $this->advance();

        while (true) {
            // Skip irrelevant tokens.
            while (
                !in_array(
                    $this->token['code'],
                    array_merge(
                        [T_NAMESPACE, T_USE],
                        Tokens::$methodPrefixes,
                        [T_READONLY],
                        Tokens::$ooScopeTokens,
                        [T_FUNCTION, T_CLOSURE, T_FN,
                        T_VAR, T_CONST,
                        null]
                    )
                )
                && !($this->fileptr >= $scope->closer)
            ) {
                $this->advance();
            }


            if ($this->fileptr >= $scope->closer) {
                // End of the block.
                break;
            } elseif ($this->token['code'] == T_NAMESPACE && $scope->type == 'root') {
                // Namespace.
                $this->processNamespace($scope);
            } elseif ($this->token['code'] == T_USE) {
                // Use.
                if ($scope->type == 'root' | $scope->type == 'namespace') {
                    $this->processUse($scope);
                } elseif ($scope->type == 'classish') {
                    $this->processClassTraitUse();
                } else {
                    throw new \Exception();
                }
            } elseif (
                in_array(
                    $this->token['code'],
                    array_merge(
                        Tokens::$methodPrefixes,
                        [T_READONLY],
                        Tokens::$ooScopeTokens,
                        [T_FUNCTION, T_CLOSURE, T_FN,
                        T_CONST, T_VAR, ]
                    )
                )
            ) {
                // Declarations.
                // Fetch comment.
                $this->comment = $this->commentpending;
                $this->commentpending = null;
                // Ignore preceding stuff, and gather info to check this is actually a declaration.
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
                // What kind of declaration is this?
                if ($static && ($this->token['code'] == T_DOUBLE_COLON || $staticprecededbynew)) {
                    // It's not a declaration, it's a static late binding.  Ignore.
                } elseif (in_array($this->token['code'], Tokens::$ooScopeTokens)) {
                    // Classish thing.
                    $this->processClassish($scope);
                } elseif (in_array($this->token['code'], [T_FUNCTION, T_CLOSURE, T_FN])) {
                    // Function.
                    $this->processFunction($scope);
                } else {
                    // Variable.
                    $this->processVariable($scope);
                }
                $this->comment = null;
            } else {
                // We got something unrecognised.
                throw new \Exception();
            }
        }

        // Check we are at the end of the scope.
        if ($this->fileptr != $scope->closer) {
            throw new \Exception();
        }
        // We can't consume this token.  Arrow functions close on the token following their body.
        /*if ($this->token['code']) {
            $this->advance();
        }*/
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
            && in_array(
                $this->tokens[$nextptr]['code'],
                array_merge([T_WHITESPACE, T_COMMENT], Tokens::$phpcsCommentTokens)
            )
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

        // Allow attributes between the comment and what it relates to.
        while (
            $nextptr < count($this->tokens)
            && in_array($this->tokens[$nextptr]['code'], [T_WHITESPACE, T_ATTRIBUTE])
        ) {
            if ($this->tokens[$nextptr]['code'] == T_ATTRIBUTE) {
                $nextptr = $this->tokens[$nextptr]['attribute_closer'] + 1;
            } else {
                $nextptr++;
            }
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
     * Advance the token pointer to a specific point.
     * @param int $newptr
     * @return void
     * @phpstan-impure
     */
    protected function advanceTo(int $newptr): void {
        $this->fileptr = $newptr;
        $this->commentpending = null;
        $this->commentpendingcounter = 0;
        $this->fetchToken();
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
                T_DOC_COMMENT_TAG, T_DOC_COMMENT_STRING, T_DOC_COMMENT_WHITESPACE]
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
        $this->commentpending = (object)['ptr' => $this->fileptr, 'tags' => []];

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
            $tag = (object)['ptr' => $this->fileptr, 'content' => '', 'cstartptr' => null, 'cendptr' => null];
            // Fetch the tag type.
            if ($this->token['code'] == T_DOC_COMMENT_TAG) {
                $tagtype = $this->token['content'];
                $this->advanceComment(T_DOC_COMMENT_TAG);
                while (
                    $this->token['code'] == T_DOC_COMMENT_WHITESPACE
                    && !in_array(substr($this->token['content'], -1), ["\n", "\r"])
                ) {
                    $this->advanceComment(T_DOC_COMMENT_WHITESPACE);
                }
            } else {
                $tagtype = '';
            }

            // For each line, until we reach a new tag.
            // Note: the logic for fixing a comment tag must exactly match this.
            do {
                $newline = false;
                // Fetch line content.
                while ($this->token['code'] != T_DOC_COMMENT_CLOSE_TAG && !$newline) {
                    if (!$tag->cstartptr) {
                        $tag->cstartptr = $this->fileptr;
                    }
                    $tag->cendptr = $this->fileptr;
                    $newline = in_array(substr($this->token['content'], -1), ["\n", "\r"]);
                    $tag->content .= ($newline ? "\n" : $this->token['content']);
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
            $this->commentpending->tags[$tagtype][] = $tag;
        }
        $this->advanceComment(T_DOC_COMMENT_CLOSE_TAG);
    }

    /**
     * Fix a PHPDoc comment tag.
     * @param object{ptr: int, content: string, cstartptr: ?int, cendptr: ?int} $tag
     * @param string $replacement
     * @return void
     * @phpstan-impure
     */
    protected function fixCommentTag(object $tag, string $replacement): void {
        $replacementarray = explode("\n", $replacement);
        $replacementcounter = 0;
        $donereplacement = false;
        $ptr = $tag->cstartptr;

        $this->file->fixer->beginChangeset();

        // For each line, until we reach a new tag.
        // Note: the logic for this must exactly match that for processing a comment tag.
        do {
            $newline = false;
            // Change line content.
            while ($this->tokens[$ptr]['code'] != T_DOC_COMMENT_CLOSE_TAG && !$newline) {
                $newline = in_array(substr($this->tokens[$ptr]['content'], -1), ["\n", "\r"]);
                if (!$newline) {
                    if ($donereplacement || $replacementarray[$replacementcounter] === "") {
                        throw new \Exception();
                    }
                    $this->file->fixer->replaceToken($ptr, $replacementarray[$replacementcounter]);
                    $donereplacement = true;
                } else {
                    if (!($donereplacement || $replacementarray[$replacementcounter] === "")) {
                        throw new \Exception();
                    }
                    $replacementcounter++;
                    $donereplacement = false;
                }
                $ptr++;
            }
            // Skip next line starting stuff.
            while (
                in_array($this->tokens[$ptr]['code'], [T_DOC_COMMENT_OPEN_TAG, T_DOC_COMMENT_STAR])
                    || $this->tokens[$ptr]['code'] == T_DOC_COMMENT_WHITESPACE
                        && !in_array(substr($this->tokens[$ptr]['content'], -1), ["\n", "\r"])
            ) {
                $ptr++;
            }
        } while (!in_array($this->tokens[$ptr]['code'], [T_DOC_COMMENT_CLOSE_TAG, T_DOC_COMMENT_TAG]));

        // Check we're done all the expected replacements, otherwise something's gone seriously wrong.
        if (
            !($replacementcounter == count($replacementarray) - 1
            && ($donereplacement || $replacementarray[count($replacementarray) - 1] === ""))
        ) {
            throw new \Exception();
        }

        $this->file->fixer->endChangeset();
    }

    /**
     * Process a namespace declaration.
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processNamespace(object $scope): void {
        $this->advance(T_NAMESPACE);

        // Fetch the namespace.
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

        // Check it's right.
        if ($namespace != '' && $namespace[strlen($namespace) - 1] == "\\") {
            throw new \Exception();
        }

        // Check it's fully qualified.
        if ($namespace != '' && $namespace[0] != "\\") {
            $namespace = "\\" . $namespace;
        }

        // What kind of namespace is it?
        if (!in_array($this->token['code'], [T_OPEN_CURLY_BRACKET, T_SEMICOLON])) {
            throw new \Exception();
        }
        if ($this->token['code'] == T_OPEN_CURLY_BRACKET) {
            $scope = clone($scope);
            $scope->type = 'namespace';
            $scope->namespace = $namespace;
            $this->processBlock($scope);
        } else {
            $scope->namespace = $namespace;
            $this->advance(T_SEMICOLON);
        }
    }

    /**
     * Process a use declaration.
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processUse(object $scope): void {
        $this->advance(T_USE);

        // Loop until we've fetched all imports.
        $more = false;
        do {
            // Get the type.
            $type = 'class';
            if ($this->token['code'] == T_FUNCTION) {
                $type = 'function';
                $this->advance(T_FUNCTION);
            } elseif ($this->token['code'] == T_CONST) {
                $type = 'const';
                $this->advance(T_CONST);
            }

            // Get what's being imported
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

            // Check it's fully qualified.
            if ($namespace != '' && $namespace[0] != "\\") {
                $namespace = "\\" . $namespace;
            }

            if ($this->token['code'] == T_OPEN_USE_GROUP) {
                // It's a group.
                $namespacestart = $namespace;
                if ($namespacestart && strrpos($namespacestart, "\\") != strlen($namespacestart) - 1) {
                    throw new \Exception();
                }
                $typestart = $type;

                // Fetch everything in the group.
                $this->advance(T_OPEN_USE_GROUP);
                do {
                    // Get the type.
                    $type = $typestart;
                    if ($this->token['code'] == T_FUNCTION) {
                        $type = 'function';
                        $this->advance(T_FUNCTION);
                    } elseif ($this->token['code'] == T_CONST) {
                        $type = 'const';
                        $this->advance(T_CONST);
                    }

                    // Get what's being imported.
                    $namespaceend = '';
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

                    // Figure out the alias.
                    $alias = substr($namespace, strrpos($namespace, "\\") + 1);
                    $asalias = $this->processUseAsAlias();
                    $alias = $asalias ?? $alias;

                    // Store it.
                    if ($this->pass == 2 && $type == 'class') {
                        $scope->uses[$alias] = $namespace;
                    }

                    $more = ($this->token['code'] == T_COMMA);
                    if ($more) {
                        $this->advance(T_COMMA);
                    }
                } while ($more);
                $this->advance(T_CLOSE_USE_GROUP);
            } else {
                // It's a single import.
                // Figure out the alias.
                $alias = (strrpos($namespace, "\\") !== false) ?
                    substr($namespace, strrpos($namespace, "\\") + 1)
                    : $namespace;
                if ($alias == '') {
                    throw new \Exception();
                }
                $asalias = $this->processUseAsAlias();
                $alias = $asalias ?? $alias;

                // Store it.
                if ($this->pass == 2 && $type == 'class') {
                    $scope->uses[$alias] = $namespace;
                }
            }
            $more = ($this->token['code'] == T_COMMA);
            if ($more) {
                $this->advance(T_COMMA);
            }
        } while ($more);

        $this->advance(T_SEMICOLON);
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
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processClassish(object $scope): void {

        // New scope.
        $scope = clone($scope);
        $scope->type = 'classish';
        $scope->closer = null;

        // Get details.
        $name = $this->file->getDeclarationName($this->fileptr);
        $name = $name ? $scope->namespace . "\\" . $name : null;
        $parent = $this->file->findExtendedClassName($this->fileptr);
        if ($parent && $parent[0] != "\\") {
            $parent = $scope->namespace . "\\" . $parent;
        }
        $interfaces = $this->file->findImplementedInterfaceNames($this->fileptr);
        if (!is_array($interfaces)) {
            $interfaces = [];
        }
        foreach ($interfaces as $index => $interface) {
            if ($interface && $interface[0] != "\\") {
                $interfaces[$index] = $scope->namespace . "\\" . $interface;
            }
        }
        $scope->classname = $name;
        $scope->parentname = $parent;

        if ($this->pass == 1 && $name) {
            // Store details.
            $this->artifacts[$name] = (object)['extends' => $parent, 'implements' => $interfaces];
        } elseif ($this->pass == 2) {
            // Check and store templates.
            if ($this->comment && isset($this->comment->tags['@template'])) {
                $this->processTemplates($scope);
            }
            // Check properties.
            if ($this->comment) {
                // Check each property type.
                foreach (['@property', '@property-read', '@property-write'] as $tagname) {
                    if (!isset($this->comment->tags[$tagname])) {
                        $this->comment->tags[$tagname] = [];
                    }

                    // Check each individual property.
                    for ($propnum = 0; $propnum < count($this->comment->tags[$tagname]); $propnum++) {
                        $docpropdata = $this->typeparser->parseTypeAndVar(
                            $scope,
                            $this->comment->tags[$tagname][$propnum]->content,
                            1,
                            false
                        );
                        if (!$docpropdata->type) {
                            $this->file->addError(
                                'PHPDoc class property type missing or malformed',
                                $this->comment->tags[$tagname][$propnum]->ptr,
                                'phpdoc_class_prop_type'
                            );
                        } elseif (!$docpropdata->var) {
                            $this->file->addError(
                                'PHPDoc class property name missing or malformed',
                                $this->comment->tags[$tagname][$propnum]->ptr,
                                'phpdoc_class_prop_name'
                            );
                        } elseif ($docpropdata->fixed) {
                            $fix = $this->file->addFixableWarning(
                                "PHPDoc class property type doesn't conform to recommended style",
                                $this->comment->tags[$tagname][$propnum]->ptr,
                                'phpdoc_class_prop_type_style'
                            );
                            if ($fix) {
                                $this->fixCommentTag(
                                    $this->comment->tags[$tagname][$propnum],
                                    $docpropdata->fixed
                                );
                            }
                        }
                    }
                }
            }
        }

        $parametersptr = isset($this->token['parenthesis_opener']) ? $this->token['parenthesis_opener'] : null;
        $blockptr = isset($this->token['scope_opener']) ? $this->token['scope_opener'] : null;

        $this->advance();

        // If it's an anonymous class, it could have parameters.
        // And those parameters could have other anonymous classes or functions in them.
        if ($parametersptr) {
            $this->advanceTo($parametersptr);
            $this->processParameters($scope);
        }

        // Process the content.
        if ($blockptr) {
            $this->advanceTo($blockptr);
            $this->processBlock($scope);
        };
    }

    /**
     * Skip over a class trait usage.
     * We need to ignore these, because if it's got public, protected, or private in it,
     * it could be confused for a declaration.
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
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processFunction(object $scope): void {

        // New scope.
        $scope = clone($scope);
        $scope->type = 'function';
        $scope->closer = null;

        // Get details.
        // Can't fetch name for arrow functions.  But we're not doing checks that need the name any more.
        // $name = $this->file->getDeclarationName($this->fileptr);
        $parameters = $this->file->getMethodParameters($this->fileptr);
        $properties = $this->file->getMethodProperties($this->fileptr);

        // Checks.
        if ($this->pass == 2) {
            // Check for missing docs if not anonymous.
            /*if ($name && !$this->comment) {
                $this->file->addWarning(
                    'PHPDoc function is not documented',
                    $this->fileptr,
                    'phpdoc_fun_doc_missing'
                );
            }*/

            // Check and store templates.
            if ($this->comment && isset($this->comment->tags['@template'])) {
                $this->processTemplates($scope);
            }

            // Check parameter types.
            if ($this->comment && isset($parameters)) {
                if (!isset($this->comment->tags['@param'])) {
                    $this->comment->tags['@param'] = [];
                }
                if (count($this->comment->tags['@param']) != count($parameters)) {
                    $this->file->addError(
                        "PHPDoc number of function @param tags doesn't match actual number of parameters",
                        $this->comment->ptr,
                        'phpdoc_fun_param_count'
                    );
                }

                // Check each individual parameter.
                for ($varnum = 0; $varnum < count($this->comment->tags['@param']); $varnum++) {
                    $docparamdata = $this->typeparser->parseTypeAndVar(
                        $scope,
                        $this->comment->tags['@param'][$varnum]->content,
                        2,
                        false
                    );
                    if (!$docparamdata->type) {
                        $this->file->addError(
                            'PHPDoc function parameter %s type missing or malformed',
                            $this->comment->tags['@param'][$varnum]->ptr,
                            'phpdoc_fun_param_type',
                            [$varnum + 1]
                        );
                    } elseif (!$docparamdata->var) {
                        $this->file->addError(
                            'PHPDoc function parameter %s name missing or malformed',
                            $this->comment->tags['@param'][$varnum]->ptr,
                            'phpdoc_fun_param_name',
                            [$varnum + 1]
                        );
                    } elseif ($varnum < count($parameters)) {
                        // Compare docs against actual parameters.
                        $paramdata = $this->typeparser->parseTypeAndVar(
                            $scope,
                            $parameters[$varnum]['content'],
                            3,
                            true
                        );
                        if ($paramdata->var != $docparamdata->var) {
                            // Function parameter names don't match.
                            // Don't do any more checking, because the parameters might be in the wrong order.
                            $this->file->addError(
                                'PHPDoc function parameter %s name mismatch',
                                $this->comment->tags['@param'][$varnum]->ptr,
                                'phpdoc_fun_param_name_mismatch',
                                [$varnum + 1]
                            );
                        } else {
                            if (!$this->typeparser->comparetypes($paramdata->type, $docparamdata->type)) {
                                $this->file->addError(
                                    'PHPDoc function parameter %s type mismatch',
                                    $this->comment->tags['@param'][$varnum]->ptr,
                                    'phpdoc_fun_param_type_mismatch',
                                    [$varnum + 1]
                                );
                            } elseif ($docparamdata->fixed) {
                                $fix = $this->file->addFixableWarning(
                                    "PHPDoc function parameter %s type doesn't conform to recommended style",
                                    $this->comment->tags['@param'][$varnum]->ptr,
                                    'phpdoc_fun_param_type_style',
                                    [$varnum + 1]
                                );
                                if ($fix) {
                                    $this->fixCommentTag(
                                        $this->comment->tags['@param'][$varnum],
                                        $docparamdata->fixed
                                    );
                                }
                            }
                            if ($paramdata->passsplat != $docparamdata->passsplat) {
                                $this->file->addWarning(
                                    'PHPDoc function parameter %s splat mismatch',
                                    $this->comment->tags['@param'][$varnum]->ptr,
                                    'phpdoc_fun_param_pass_splat_mismatch',
                                    [$varnum + 1]
                                );
                            }
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
                        $this->comment->tags['@return'][1]->ptr,
                        'phpdoc_fun_ret_multiple'
                    );
                }
                $retdata = $properties['return_type'] ?
                    $this->typeparser->parseTypeAndVar(
                        $scope,
                        $properties['return_type'],
                        0,
                        true
                    )
                    : (object)['type' => 'mixed'];

                // Check each individual return tag, in case there's more than one.
                for ($retnum = 0; $retnum < count($this->comment->tags['@return']); $retnum++) {
                    $docretdata = $this->typeparser->parseTypeAndVar(
                        $scope,
                        $this->comment->tags['@return'][$retnum]->content,
                        0,
                        false
                    );
                    if (!$docretdata->type) {
                        $this->file->addError(
                            'PHPDoc function return type missing or malformed',
                            $this->comment->tags['@return'][$retnum]->ptr,
                            'phpdoc_fun_ret_type'
                        );
                    } elseif (!$this->typeparser->comparetypes($retdata->type, $docretdata->type)) {
                        $this->file->addError(
                            'PHPDoc function return type mismatch',
                            $this->comment->tags['@return'][$retnum]->ptr,
                            'phpdoc_fun_ret_type_mismatch'
                        );
                    } elseif ($docretdata->fixed) {
                        $fix = $this->file->addFixableWarning(
                            "PHPDoc function return type doesn't conform to recommended style",
                            $this->comment->tags['@return'][$retnum]->ptr,
                            'phpdoc_fun_ret_type_style'
                        );
                        if ($fix) {
                            $this->fixCommentTag(
                                $this->comment->tags['@return'][$retnum],
                                $docretdata->fixed
                            );
                        }
                    }
                }
            }
        }

        $parametersptr = isset($this->token['parenthesis_opener']) ? $this->token['parenthesis_opener'] : null;
        $blockptr = isset($this->token['scope_opener']) ? $this->token['scope_opener'] : null;

        $this->advance();

        // Parameters could contain anonymous classes or functions.
        if ($parametersptr) {
            $this->advanceTo($parametersptr);
            $this->processParameters($scope);
        }

        // Content.
        if ($blockptr) {
            $this->advanceTo($blockptr);
            $this->processBlock($scope);
        };
    }

    /**
     * Search parameter default values for anonymous classes and functions
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processParameters(object $scope): void {

        $scope = clone($scope);
        $scope->closer = $this->token['parenthesis_closer'];
        $this->advance(T_OPEN_PARENTHESIS);

        while (true) {
            // Skip irrelevant tokens.
            while (
                !in_array($this->token['code'], [T_ANON_CLASS, T_CLOSURE, T_FN])
                && $this->fileptr < $scope->closer
            ) {
                $this->advance();
            }

            if ($this->fileptr >= $scope->closer) {
                // End of the parameters.
                break;
            } elseif ($this->token['code'] == T_ANON_CLASS) {
                // Classish thing.
                $this->processClassish($scope);
            } elseif (in_array($this->token['code'], [T_CLOSURE, T_FN])) {
                // Function.
                $this->processFunction($scope);
            } else {
                // Something unrecognised.
                throw new \Exception();
            }
        }
        $this->advance(T_CLOSE_PARENTHESIS);
    }


    /**
     * Process templates.
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processTemplates(object $scope): void {
        foreach ($this->comment->tags['@template'] as $templatetag) {
            $templatedata = $this->typeparser->parseTemplate($scope, $templatetag->content);
            if (!$templatedata->var) {
                $this->file->addError('PHPDoc template name missing or malformed', $templatetag->ptr, 'phpdoc_template_name');
            } elseif (!$templatedata->type) {
                $this->file->addError('PHPDoc template type missing or malformed', $templatetag->ptr, 'phpdoc_template_type');
                $scope->templates[$templatedata->var] = 'never';
            } else {
                $scope->templates[$templatedata->var] = $templatedata->type;
                if ($templatedata->fixed) {
                    $fix = $this->file->addFixableWarning(
                        "PHPDoc tempate type doesn't conform to recommended style",
                        $templatetag->ptr,
                        'phpdoc_template_type_style'
                    );
                    if ($fix) {
                        $this->fixCommentTag(
                            $templatetag,
                            $templatedata->fixed
                        );
                    }
                }
            }
        }
    }

    /**
     * Process a variable.
     * @param \stdClass&object{namespace: string, uses: string[], templates: string[],
     *              classname: ?string, parentname: ?string, type: string, closer: ?int} $scope
     * @return void
     * @phpstan-impure
     */
    protected function processVariable($scope): void {

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

        // Type checking.
        if ($this->pass == 2) {
            // Get properties, unless it's a function static variable or constant.
            $properties = ($scope->type == 'classish' && !$const) ?
                $this->file->getMemberProperties($this->fileptr)
                : null;

            if (!$this->comment && $scope->type == 'classish') {
                // Require comments for class variables and constants.
                /*$this->file->addWarning(
                    'PHPDoc variable or constant is not documented',
                    $this->fileptr,
                    'phpdoc_var_doc_missing'
                );*/
            } elseif ($this->comment) {
                if (!isset($this->comment->tags['@var'])) {
                    $this->comment->tags['@var'] = [];
                }
                // Missing or multiple vars.
                /*if (count($this->comment->tags['@var']) < 1) {
                    $this->file->addError('PHPDoc missing @var tag', $this->comment->ptr, 'phpdoc_var_missing');
                } elseif (count($this->comment->tags['@var']) > 1) {
                    $this->file->addError('PHPDoc multiple @var tags', $this->comment->tags['@var'][1]->ptr, 'phpdoc_var_multiple');
                }*/
                // Var type check and match.
                $vardata = ($properties && $properties['type']) ?
                    $this->typeparser->parseTypeAndVar(
                        $scope,
                        $properties['type'],
                        0,
                        true
                    )
                    : (object)['type' => 'mixed'];
                for ($varnum = 0; $varnum < count($this->comment->tags['@var']); $varnum++) {
                    $docvardata = $this->typeparser->parseTypeAndVar(
                        $scope,
                        $this->comment->tags['@var'][$varnum]->content,
                        0,
                        false
                    );
                    if (!$docvardata->type) {
                        $this->file->addError(
                            'PHPDoc var type missing or malformed',
                            $this->comment->tags['@var'][$varnum]->ptr,
                            'phpdoc_var_type'
                        );
                    } elseif (!$this->typeparser->comparetypes($vardata->type, $docvardata->type)) {
                        $this->file->addError(
                            'PHPDoc var type mismatch',
                            $this->comment->tags['@var'][$varnum]->ptr,
                            'phpdoc_var_type_mismatch'
                        );
                    } elseif ($docvardata->fixed) {
                        $fix = $this->file->addFixableWarning(
                            "PHPDoc var type doesn't conform to recommended style",
                            $this->comment->tags['@var'][$varnum]->ptr,
                            'phpdoc_var_type_style'
                        );
                        if ($fix) {
                            $this->fixCommentTag(
                                $this->comment->tags['@var'][$varnum],
                                $docvardata->fixed
                            );
                        }
                    }
                }
            }
        }

        $this->advance();

        if (!in_array($this->token['code'], [T_EQUAL, T_COMMA, T_SEMICOLON])) {
            throw new \Exception();
        }
        $this->advance();
    }
}
