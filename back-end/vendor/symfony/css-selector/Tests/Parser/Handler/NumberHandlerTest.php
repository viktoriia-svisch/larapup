<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Handler;
use Symfony\Component\CssSelector\Parser\Handler\NumberHandler;
use Symfony\Component\CssSelector\Parser\Token;
use Symfony\Component\CssSelector\Parser\Tokenizer\TokenizerPatterns;
class NumberHandlerTest extends AbstractHandlerTest
{
    public function getHandleValueTestData()
    {
        return [
            ['12', new Token(Token::TYPE_NUMBER, '12', 0), ''],
            ['12.34', new Token(Token::TYPE_NUMBER, '12.34', 0), ''],
            ['+12.34', new Token(Token::TYPE_NUMBER, '+12.34', 0), ''],
            ['-12.34', new Token(Token::TYPE_NUMBER, '-12.34', 0), ''],
            ['12 arg', new Token(Token::TYPE_NUMBER, '12', 0), ' arg'],
            ['12]', new Token(Token::TYPE_NUMBER, '12', 0), ']'],
        ];
    }
    public function getDontHandleValueTestData()
    {
        return [
            ['hello'],
            ['>'],
            ['+'],
            [' '],
            [''],
        ];
    }
    protected function generateHandler()
    {
        $patterns = new TokenizerPatterns();
        return new NumberHandler($patterns);
    }
}
