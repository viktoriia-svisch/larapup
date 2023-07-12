<?php
namespace Symfony\Component\Console\Tests\Helper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Helper\TableStyle;
class TableStyleTest extends TestCase
{
    public function testSetPadTypeWithInvalidType()
    {
        $style = new TableStyle();
        $style->setPadType('TEST');
    }
}
