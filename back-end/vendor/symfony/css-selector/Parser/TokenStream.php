<?php
namespace Symfony\Component\CssSelector\Parser;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
class TokenStream
{
    private $tokens = [];
    private $used = [];
    private $cursor = 0;
    private $peeked;
    private $peeking = false;
    public function push(Token $token)
    {
        $this->tokens[] = $token;
        return $this;
    }
    public function freeze()
    {
        return $this;
    }
    public function getNext()
    {
        if ($this->peeking) {
            $this->peeking = false;
            $this->used[] = $this->peeked;
            return $this->peeked;
        }
        if (!isset($this->tokens[$this->cursor])) {
            throw new InternalErrorException('Unexpected token stream end.');
        }
        return $this->tokens[$this->cursor++];
    }
    public function getPeek()
    {
        if (!$this->peeking) {
            $this->peeked = $this->getNext();
            $this->peeking = true;
        }
        return $this->peeked;
    }
    public function getUsed()
    {
        return $this->used;
    }
    public function getNextIdentifier()
    {
        $next = $this->getNext();
        if (!$next->isIdentifier()) {
            throw SyntaxErrorException::unexpectedToken('identifier', $next);
        }
        return $next->getValue();
    }
    public function getNextIdentifierOrStar()
    {
        $next = $this->getNext();
        if ($next->isIdentifier()) {
            return $next->getValue();
        }
        if ($next->isDelimiter(['*'])) {
            return;
        }
        throw SyntaxErrorException::unexpectedToken('identifier or "*"', $next);
    }
    public function skipWhitespace()
    {
        $peek = $this->getPeek();
        if ($peek->isWhitespace()) {
            $this->getNext();
        }
    }
}
