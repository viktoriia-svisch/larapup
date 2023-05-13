<?php
namespace Symfony\Component\CssSelector\Parser\Handler;
use Symfony\Component\CssSelector\Exception\InternalErrorException;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use Symfony\Component\CssSelector\Parser\Reader;
use Symfony\Component\CssSelector\Parser\Token;
use Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerEscaping;
use Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns;
use Symfony\Component\CssSelector\Parser\TokenStream;
class StringHandler implements HandlerInterface
{
    private $patterns;
    private $escaping;
    public function __construct(TokenizerPatterns $patterns, TokenizerEscaping $escaping)
    {
        $this->patterns = $patterns;
        $this->escaping = $escaping;
    }
    public function handle(Reader $reader, TokenStream $stream): bool
    {
        $quote = $reader->getSubstring(1);
        if (!\in_array($quote, ["'", '"'])) {
            return false;
        }
        $reader->moveForward(1);
        $match = $reader->findPattern($this->patterns->getQuotedStringPattern($quote));
        if (!$match) {
            throw new InternalErrorException(sprintf('Should have found at least an empty match at %s.', $reader->getPosition()));
        }
        if (\strlen($match[0]) === $reader->getRemainingLength()) {
            throw SyntaxErrorException::unclosedString($reader->getPosition() - 1);
        }
        if ($quote !== $reader->getSubstring(1, \strlen($match[0]))) {
            throw SyntaxErrorException::unclosedString($reader->getPosition() - 1);
        }
        $string = $this->escaping->escapeUnicodeAndNewLine($match[0]);
        $stream->push(new Token(Token::TYPE_STRING, $string, $reader->getPosition()));
        $reader->moveForward(\strlen($match[0]) + 1);
        return true;
    }
}
