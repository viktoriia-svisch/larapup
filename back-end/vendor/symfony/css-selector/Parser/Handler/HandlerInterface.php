<?php
namespace Symfony\Component\CssSelector\Parser\Handler;
use Symfony\Component\CssSelector\Parser\Reader;
use Symfony\Component\CssSelector\Parser\TokenStream;
interface HandlerInterface
{
    public function handle(Reader $reader, TokenStream $stream): bool;
}
