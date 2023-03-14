<?php declare(strict_types = 1);
namespace TheSeer\Tokenizer;
class TokenCollection implements \ArrayAccess, \Iterator, \Countable {
    private $tokens = [];
    private $pos;
    public function addToken(Token $token) {
        $this->tokens[] = $token;
    }
    public function current(): Token {
        return current($this->tokens);
    }
    public function key(): int {
        return key($this->tokens);
    }
    public function next() {
        next($this->tokens);
        $this->pos++;
    }
    public function valid(): bool {
        return $this->count() > $this->pos;
    }
    public function rewind() {
        reset($this->tokens);
        $this->pos = 0;
    }
    public function count(): int {
        return count($this->tokens);
    }
    public function offsetExists($offset): bool {
        return isset($this->tokens[$offset]);
    }
    public function offsetGet($offset): Token {
        if (!$this->offsetExists($offset)) {
            throw new TokenCollectionException(
                sprintf('No Token at offest %s', $offset)
            );
        }
        return $this->tokens[$offset];
    }
    public function offsetSet($offset, $value) {
        if (!is_int($offset)) {
            $type = gettype($offset);
            throw new TokenCollectionException(
                sprintf(
                    'Offset must be of type integer, %s given',
                    $type === 'object' ? get_class($value) : $type
                )
            );
        }
        if (!$value instanceof Token) {
            $type = gettype($value);
            throw new TokenCollectionException(
                sprintf(
                    'Value must be of type %s, %s given',
                    Token::class,
                    $type === 'object' ? get_class($value) : $type
                )
            );
        }
        $this->tokens[$offset] = $value;
    }
    public function offsetUnset($offset) {
        unset($this->tokens[$offset]);
    }
}
