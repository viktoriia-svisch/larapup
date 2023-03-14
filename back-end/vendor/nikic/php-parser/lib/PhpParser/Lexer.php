<?php declare(strict_types=1);
namespace PhpParser;
use PhpParser\Parser\Tokens;
class Lexer
{
    protected $code;
    protected $tokens;
    protected $pos;
    protected $line;
    protected $filePos;
    protected $prevCloseTagHasNewline;
    protected $tokenMap;
    protected $dropTokens;
    protected $usedAttributes;
    public function __construct(array $options = []) {
        $this->tokenMap = $this->createTokenMap();
        $this->dropTokens = array_fill_keys(
            [\T_WHITESPACE, \T_OPEN_TAG, \T_COMMENT, \T_DOC_COMMENT], 1
        );
        $options += [
            'usedAttributes' => ['comments', 'startLine', 'endLine'],
        ];
        $this->usedAttributes = array_fill_keys($options['usedAttributes'], true);
    }
    public function startLexing(string $code, ErrorHandler $errorHandler = null) {
        if (null === $errorHandler) {
            $errorHandler = new ErrorHandler\Throwing();
        }
        $this->code = $code; 
        $this->pos  = -1;
        $this->line =  1;
        $this->filePos = 0;
        $this->prevCloseTagHasNewline = true;
        $scream = ini_set('xdebug.scream', '0');
        error_clear_last();
        $this->tokens = @token_get_all($code);
        $this->handleErrors($errorHandler);
        if (false !== $scream) {
            ini_set('xdebug.scream', $scream);
        }
    }
    private function handleInvalidCharacterRange($start, $end, $line, ErrorHandler $errorHandler) {
        for ($i = $start; $i < $end; $i++) {
            $chr = $this->code[$i];
            if ($chr === 'b' || $chr === 'B') {
                continue;
            }
            if ($chr === "\0") {
                $errorMsg = 'Unexpected null byte';
            } else {
                $errorMsg = sprintf(
                    'Unexpected character "%s" (ASCII %d)', $chr, ord($chr)
                );
            }
            $errorHandler->handleError(new Error($errorMsg, [
                'startLine' => $line,
                'endLine' => $line,
                'startFilePos' => $i,
                'endFilePos' => $i,
            ]));
        }
    }
    private function isUnterminatedComment($token) : bool {
        return ($token[0] === \T_COMMENT || $token[0] === \T_DOC_COMMENT)
            && substr($token[1], 0, 2) === '';
    }
    private function errorMayHaveOccurred() : bool {
        if (defined('HHVM_VERSION')) {
            return true;
        }
        return null !== error_get_last();
    }
    protected function handleErrors(ErrorHandler $errorHandler) {
        if (!$this->errorMayHaveOccurred()) {
            return;
        }
        $filePos = 0;
        $line = 1;
        foreach ($this->tokens as $token) {
            $tokenValue = \is_string($token) ? $token : $token[1];
            $tokenLen = \strlen($tokenValue);
            if (substr($this->code, $filePos, $tokenLen) !== $tokenValue) {
                $nextFilePos = strpos($this->code, $tokenValue, $filePos);
                $this->handleInvalidCharacterRange(
                    $filePos, $nextFilePos, $line, $errorHandler);
                $filePos = (int) $nextFilePos;
            }
            $filePos += $tokenLen;
            $line += substr_count($tokenValue, "\n");
        }
        if ($filePos !== \strlen($this->code)) {
            if (substr($this->code, $filePos, 2) === '
    public function getNextToken(&$value = null, &$startAttributes = null, &$endAttributes = null) : int {
        $startAttributes = [];
        $endAttributes   = [];
        while (1) {
            if (isset($this->tokens[++$this->pos])) {
                $token = $this->tokens[$this->pos];
            } else {
                $token = "\0";
            }
            if (isset($this->usedAttributes['startLine'])) {
                $startAttributes['startLine'] = $this->line;
            }
            if (isset($this->usedAttributes['startTokenPos'])) {
                $startAttributes['startTokenPos'] = $this->pos;
            }
            if (isset($this->usedAttributes['startFilePos'])) {
                $startAttributes['startFilePos'] = $this->filePos;
            }
            if (\is_string($token)) {
                $value = $token;
                if (isset($token[1])) {
                    $this->filePos += 2;
                    $id = ord('"');
                } else {
                    $this->filePos += 1;
                    $id = ord($token);
                }
            } elseif (!isset($this->dropTokens[$token[0]])) {
                $value = $token[1];
                $id = $this->tokenMap[$token[0]];
                if (\T_CLOSE_TAG === $token[0]) {
                    $this->prevCloseTagHasNewline = false !== strpos($token[1], "\n");
                } elseif (\T_INLINE_HTML === $token[0]) {
                    $startAttributes['hasLeadingNewline'] = $this->prevCloseTagHasNewline;
                }
                $this->line += substr_count($value, "\n");
                $this->filePos += \strlen($value);
            } else {
                if (\T_COMMENT === $token[0] || \T_DOC_COMMENT === $token[0]) {
                    if (isset($this->usedAttributes['comments'])) {
                        $comment = \T_DOC_COMMENT === $token[0]
                            ? new Comment\Doc($token[1], $this->line, $this->filePos, $this->pos)
                            : new Comment($token[1], $this->line, $this->filePos, $this->pos);
                        $startAttributes['comments'][] = $comment;
                    }
                }
                $this->line += substr_count($token[1], "\n");
                $this->filePos += \strlen($token[1]);
                continue;
            }
            if (isset($this->usedAttributes['endLine'])) {
                $endAttributes['endLine'] = $this->line;
            }
            if (isset($this->usedAttributes['endTokenPos'])) {
                $endAttributes['endTokenPos'] = $this->pos;
            }
            if (isset($this->usedAttributes['endFilePos'])) {
                $endAttributes['endFilePos'] = $this->filePos - 1;
            }
            return $id;
        }
        throw new \RuntimeException('Reached end of lexer loop');
    }
    public function getTokens() : array {
        return $this->tokens;
    }
    public function handleHaltCompiler() : string {
        $textAfter = substr($this->code, $this->filePos);
        if (!preg_match('~^\s*\(\s*\)\s*(?:;|\?>\r?\n?)~', $textAfter, $matches)) {
            throw new Error('__HALT_COMPILER must be followed by "();"');
        }
        $this->pos = count($this->tokens);
        return substr($textAfter, strlen($matches[0]));
    }
    protected function createTokenMap() : array {
        $tokenMap = [];
        for ($i = 256; $i < 1000; ++$i) {
            if (\T_DOUBLE_COLON === $i) {
                $tokenMap[$i] = Tokens::T_PAAMAYIM_NEKUDOTAYIM;
            } elseif(\T_OPEN_TAG_WITH_ECHO === $i) {
                $tokenMap[$i] = Tokens::T_ECHO;
            } elseif(\T_CLOSE_TAG === $i) {
                $tokenMap[$i] = ord(';');
            } elseif ('UNKNOWN' !== $name = token_name($i)) {
                if ('T_HASHBANG' === $name) {
                    $tokenMap[$i] = Tokens::T_INLINE_HTML;
                } elseif (defined($name = Tokens::class . '::' . $name)) {
                    $tokenMap[$i] = constant($name);
                }
            }
        }
        if (defined('T_ONUMBER')) {
            $tokenMap[\T_ONUMBER] = Tokens::T_DNUMBER;
        }
        if (defined('T_COMPILER_HALT_OFFSET')) {
            $tokenMap[\T_COMPILER_HALT_OFFSET] = Tokens::T_STRING;
        }
        return $tokenMap;
    }
}
