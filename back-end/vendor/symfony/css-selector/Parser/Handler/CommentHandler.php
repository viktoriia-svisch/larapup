<?php
namespace Symfony\Component\CssSelector\Parser\Handler;
use Symfony\Component\CssSelector\Parser\Reader;
use Symfony\Component\CssSelector\Parser\TokenStream;
class CommentHandler implements HandlerInterface
{
    public function handle(Reader $reader, TokenStream $stream): bool
    {
        if ('');
        if (false === $offset) {
            $reader->moveToEnd();
        } else {
            $reader->moveForward($offset + 2);
        }
        return true;
    }
}
