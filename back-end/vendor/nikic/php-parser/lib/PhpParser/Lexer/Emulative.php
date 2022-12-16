<?php declare(strict_types=1);
namespace PhpParser\Lexer;
use PhpParser\Error;
use PhpParser\ErrorHandler;
use PhpParser\Parser;
class Emulative extends \PhpParser\Lexer
{
    const PHP_7_3 = '7.3.0dev';
    const PHP_7_4 = '7.4.0dev';
    const FLEXIBLE_DOC_STRING_REGEX = <<<'REGEX'
/<<<[ \t]*(['"]?)([a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*)\1\r?\n
(?:.*\r?\n)*?
(?<indentation>\h*)\2(?![a-zA-Z_\x80-\xff])(?<separator>(?:;?[\r\n])?)/x
REGEX;
    const T_COALESCE_EQUAL = 1007;
    private $patches = [];
    public function __construct(array $options = [])
    {
        parent::__construct($options);
        $this->tokenMap[self::T_COALESCE_EQUAL] = Parser\Tokens::T_COALESCE_EQUAL;
    }
    public function startLexing(string $code, ErrorHandler $errorHandler = null) {
        $this->patches = [];
        if ($this->isEmulationNeeded($code) === false) {
            parent::startLexing($code, $errorHandler);
            return;
        }
        $collector = new ErrorHandler\Collecting();
        $preparedCode = $this->processHeredocNowdoc($code);
        parent::startLexing($preparedCode, $collector);
        $this->processCoaleseEqual($code);
        $this->fixupTokens();
        $errors = $collector->getErrors();
        if (!empty($errors)) {
            $this->fixupErrors($errors);
            foreach ($errors as $error) {
                $errorHandler->handleError($error);
            }
        }
    }
    private function isCoalesceEqualEmulationNeeded(string $code): bool
    {
        if (version_compare(\PHP_VERSION, self::PHP_7_4, '>=')) {
            return false;
        }
        return strpos($code, '??=') !== false;
    }
    private function processCoaleseEqual(string $code)
    {
        if ($this->isCoalesceEqualEmulationNeeded($code) === false) {
            return;
        }
        $line = 1;
        for ($i = 0, $c = count($this->tokens); $i < $c; ++$i) {
            if (isset($this->tokens[$i + 1])) {
                if ($this->tokens[$i][0] === T_COALESCE && $this->tokens[$i + 1] === '=') {
                    array_splice($this->tokens, $i, 2, [
                        [self::T_COALESCE_EQUAL, '??=', $line]
                    ]);
                    $c--;
                    continue;
                }
            }
            if (\is_array($this->tokens[$i])) {
                $line += substr_count($this->tokens[$i][1], "\n");
            }
        }
    }
    private function isHeredocNowdocEmulationNeeded(string $code): bool
    {
        if (version_compare(\PHP_VERSION, self::PHP_7_3, '>=')) {
            return false;
        }
        return strpos($code, '<<<') !== false;
    }
    private function processHeredocNowdoc(string $code): string
    {
        if ($this->isHeredocNowdocEmulationNeeded($code) === false) {
            return $code;
        }
        if (!preg_match_all(self::FLEXIBLE_DOC_STRING_REGEX, $code, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE)) {
            return $code;
        }
        $posDelta = 0;
        foreach ($matches as $match) {
            $indentation = $match['indentation'][0];
            $indentationStart = $match['indentation'][1];
            $separator = $match['separator'][0];
            $separatorStart = $match['separator'][1];
            if ($indentation === '' && $separator !== '') {
                continue;
            }
            if ($indentation !== '') {
                $indentationLen = strlen($indentation);
                $code = substr_replace($code, '', $indentationStart + $posDelta, $indentationLen);
                $this->patches[] = [$indentationStart + $posDelta, 'add', $indentation];
                $posDelta -= $indentationLen;
            }
            if ($separator === '') {
                $code = substr_replace($code, "\n", $separatorStart + $posDelta, 0);
                $this->patches[] = [$separatorStart + $posDelta, 'remove', "\n"];
                $posDelta += 1;
            }
        }
        return $code;
    }
    private function isEmulationNeeded(string $code): bool
    {
        if ($this->isHeredocNowdocEmulationNeeded($code)) {
            return true;
        }
        if ($this->isCoalesceEqualEmulationNeeded($code)) {
            return true;
        }
        return false;
    }
    private function fixupTokens()
    {
        if (\count($this->patches) === 0) {
            return;
        }
        $patchIdx = 0;
        list($patchPos, $patchType, $patchText) = $this->patches[$patchIdx];
        $pos = 0;
        for ($i = 0, $c = \count($this->tokens); $i < $c; $i++) {
            $token = $this->tokens[$i];
            if (\is_string($token)) {
                $pos += \strlen($token);
                continue;
            }
            $len = \strlen($token[1]);
            $posDelta = 0;
            while ($patchPos >= $pos && $patchPos < $pos + $len) {
                $patchTextLen = \strlen($patchText);
                if ($patchType === 'remove') {
                    if ($patchPos === $pos && $patchTextLen === $len) {
                        array_splice($this->tokens, $i, 1, []);
                        $i--;
                        $c--;
                    } else {
                        $this->tokens[$i][1] = substr_replace(
                            $token[1], '', $patchPos - $pos + $posDelta, $patchTextLen
                        );
                        $posDelta -= $patchTextLen;
                    }
                } elseif ($patchType === 'add') {
                    $this->tokens[$i][1] = substr_replace(
                        $token[1], $patchText, $patchPos - $pos + $posDelta, 0
                    );
                    $posDelta += $patchTextLen;
                } else {
                    assert(false);
                }
                $patchIdx++;
                if ($patchIdx >= \count($this->patches)) {
                    return;
                }
                list($patchPos, $patchType, $patchText) = $this->patches[$patchIdx];
                $token = $this->tokens[$i];
            }
            $pos += $len;
        }
        assert(false);
    }
    private function fixupErrors(array $errors) {
        foreach ($errors as $error) {
            $attrs = $error->getAttributes();
            $posDelta = 0;
            $lineDelta = 0;
            foreach ($this->patches as $patch) {
                list($patchPos, $patchType, $patchText) = $patch;
                if ($patchPos >= $attrs['startFilePos']) {
                    break;
                }
                if ($patchType === 'add') {
                    $posDelta += strlen($patchText);
                    $lineDelta += substr_count($patchText, "\n");
                } else {
                    $posDelta -= strlen($patchText);
                    $lineDelta -= substr_count($patchText, "\n");
                }
            }
            $attrs['startFilePos'] += $posDelta;
            $attrs['endFilePos'] += $posDelta;
            $attrs['startLine'] += $lineDelta;
            $attrs['endLine'] += $lineDelta;
            $error->setAttributes($attrs);
        }
    }
}
