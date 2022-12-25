<?php
namespace PHPUnit\Util\TestDox;
class TestableCliTestDoxPrinter extends CliTestDoxPrinter
{
    private $buffer;
    public function write(string $text): void
    {
        $this->buffer .= $text;
    }
    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
