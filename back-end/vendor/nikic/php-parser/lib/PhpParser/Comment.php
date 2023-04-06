<?php declare(strict_types=1);
namespace PhpParser;
class Comment implements \JsonSerializable
{
    protected $text;
    protected $line;
    protected $filePos;
    protected $tokenPos;
    public function __construct(
        string $text, int $startLine = -1, int $startFilePos = -1, int $startTokenPos = -1
    ) {
        $this->text = $text;
        $this->line = $startLine;
        $this->filePos = $startFilePos;
        $this->tokenPos = $startTokenPos;
    }
    public function getText() : string {
        return $this->text;
    }
    public function getLine() : int {
        return $this->line;
    }
    public function getFilePos() : int {
        return $this->filePos;
    }
    public function getTokenPos() : int {
        return $this->tokenPos;
    }
    public function __toString() : string {
        return $this->text;
    }
    public function getReformattedText() {
        $text = trim($this->text);
        $newlinePos = strpos($text, "\n");
        if (false === $newlinePos) {
            return $text;
        } elseif (preg_match('((*BSR_ANYCRLF)(*ANYCRLF)^.*(?:\R\s+\*.*)+$)', $text)) {
            return preg_replace('(^\s+\*)m', ' *', $this->text);
        } elseif (preg_match('(^/\*\*?\s*[\r\n])', $text) && preg_match('(\n(\s*)\*/$)', $text, $matches)) {
            return preg_replace('(^' . preg_quote($matches[1]) . ')m', '', $text);
        } elseif (preg_match('(^/\*\*?\s*(?!\s))', $text, $matches)) {
            $prefixLen = $this->getShortestWhitespacePrefixLen(substr($text, $newlinePos + 1));
            $removeLen = $prefixLen - strlen($matches[0]);
            return preg_replace('(^\s{' . $removeLen . '})m', '', $text);
        }
        return $text;
    }
    private function getShortestWhitespacePrefixLen(string $str) : int {
        $lines = explode("\n", $str);
        $shortestPrefixLen = \INF;
        foreach ($lines as $line) {
            preg_match('(^\s*)', $line, $matches);
            $prefixLen = strlen($matches[0]);
            if ($prefixLen < $shortestPrefixLen) {
                $shortestPrefixLen = $prefixLen;
            }
        }
        return $shortestPrefixLen;
    }
    public function jsonSerialize() : array {
        $type = $this instanceof Comment\Doc ? 'Comment_Doc' : 'Comment';
        return [
            'nodeType' => $type,
            'text' => $this->text,
            'line' => $this->line,
            'filePos' => $this->filePos,
            'tokenPos' => $this->tokenPos,
        ];
    }
}
