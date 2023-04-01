<?php
namespace PHPUnit\Util\TestDox;
class TextResultPrinter extends ResultPrinter
{
    protected function startClass(string $name): void
    {
        $this->write($this->currentTestClassPrettified . "\n");
    }
    protected function onTest($name, bool $success = true): void
    {
        if ($success) {
            $this->write(' [x] ');
        } else {
            $this->write(' [ ] ');
        }
        $this->write($name . "\n");
    }
    protected function endClass(string $name): void
    {
        $this->write("\n");
    }
}
