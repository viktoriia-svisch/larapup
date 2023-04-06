<?php
namespace Symfony\Component\CssSelector\Parser\Handler;
use Symfony\Component\CssSelector\Parser\Reader;
use Symfony\Component\CssSelector\Parser\Token;
use Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns;
use Symfony\Component\CssSelector\Parser\TokenStream;
class NumberHandler implements HandlerInterface
{
    private $patterns;
    public function __construct(TokenizerPatterns $patterns)
    {
        $this->patterns = $patterns;
    }
    public function handle(Reader $reader, TokenStream $stream): bool
    {
        $match = $reader->findPattern($this->patterns->getNumberPattern());
        if (!$match) {
            return false;
        }
        $stream->push(new Token(Token::TYPE_NUMBER, $match[0], $reader->getPosition()));
        $reader->moveForward(\strlen($match[0]));
        return true;
    }
}
