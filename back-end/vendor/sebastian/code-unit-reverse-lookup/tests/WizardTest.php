<?php
namespace SebastianBergmann\CodeUnitReverseLookup;
use PHPUnit\Framework\TestCase;
class WizardTest extends TestCase
{
    private $wizard;
    protected function setUp()
    {
        $this->wizard = new Wizard;
    }
    public function testMethodCanBeLookedUp()
    {
        $this->assertEquals(
            __METHOD__,
            $this->wizard->lookup(__FILE__, __LINE__)
        );
    }
    public function testReturnsFilenameAndLineNumberAsStringWhenNotInCodeUnit()
    {
        $this->assertEquals(
            'file.php:1',
            $this->wizard->lookup('file.php', 1)
        );
    }
}
