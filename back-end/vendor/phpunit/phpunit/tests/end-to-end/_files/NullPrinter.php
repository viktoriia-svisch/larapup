<?php
namespace PHPUnit\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Util\Printer;
final class NullPrinter extends Printer implements TestListener
{
    use TestListenerDefaultImplementation;
}
