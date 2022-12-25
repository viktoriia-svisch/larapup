<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Handler;
use Symfony\Component\CssSelector\Parser\Handler\WhitespaceHandler;
use Symfony\Component\CssSelector\Parser\Token;
class WhitespaceHandlerTest extends AbstractHandlerTest
{
    public function getHandleValueTestData()
    {
        return [
            [' ', new Token(Token::TYPE_WHITESPACE, ' ', 0), ''],
            ["\n", new Token(Token::TYPE_WHITESPACE, "\n", 0), ''],
            ["\t", new Token(Token::TYPE_WHITESPACE, "\t", 0), ''],
            [' foo', new Token(Token::TYPE_WHITESPACE, ' ', 0), 'foo'],
            [' .foo', new Token(Token::TYPE_WHITESPACE, ' ', 0), '.foo'],
        ];
    }
    public function getDontHandleValueTestData()
    {
        return [
            ['>'],
            ['1'],
            ['a'],
        ];
    }
    protected function generateHandler()
    {
        return new WhitespaceHandler();
    }
}
