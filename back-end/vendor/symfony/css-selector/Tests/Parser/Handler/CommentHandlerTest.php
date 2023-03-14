<?php
namespace Symfony\Component\CssSelector\Tests\Parser\Handler;
use Symfony\Component\CssSelector\Parser\Handler\CommentHandler;
use Symfony\Component\CssSelector\Parser\Reader;
use Symfony\Component\CssSelector\Parser\Token;
use Symfony\Component\CssSelector\Parser\TokenStream;
class CommentHandlerTest extends AbstractHandlerTest
{
    public function testHandleValue($value, Token $unusedArgument, $remainingContent)
    {
        $reader = new Reader($value);
        $stream = new TokenStream();
        $this->assertTrue($this->generateHandler()->handle($reader, $stream));
        $this->assertStreamEmpty($stream);
        $this->assertRemainingContent($reader, $remainingContent);
    }
    public function getHandleValueTestData()
    {
        return [
            ['', new Token(null, null, null), ''],
            ['foo', new Token(null, null, null), 'foo'],
        ];
    }
    public function getDontHandleValueTestData()
    {
        return [
            ['>'],
            ['+'],
            [' '],
        ];
    }
    protected function generateHandler()
    {
        return new CommentHandler();
    }
}
