<?php
namespace Psy\Test;
use Psy\Configuration;
use Psy\ConsoleColorFactory;
class ConsoleColorFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConsoleColorAuto()
    {
        $colorMode = Configuration::COLOR_MODE_AUTO;
        $factory   = new ConsoleColorFactory($colorMode);
        $colors    = $factory->getConsoleColor();
        $themes    = $colors->getThemes();
        $this->assertFalse($colors->isStyleForced());
        $this->assertSame(['blue'], $themes['line_number']);
    }
    public function testGetConsoleColorForced()
    {
        $colorMode = Configuration::COLOR_MODE_FORCED;
        $factory   = new ConsoleColorFactory($colorMode);
        $colors    = $factory->getConsoleColor();
        $themes    = $colors->getThemes();
        $this->assertTrue($colors->isStyleForced());
        $this->assertSame(['blue'], $themes['line_number']);
    }
    public function testGetConsoleColorDisabled()
    {
        $colorMode = Configuration::COLOR_MODE_DISABLED;
        $factory   = new ConsoleColorFactory($colorMode);
        $colors    = $factory->getConsoleColor();
        $themes    = $colors->getThemes();
        $this->assertFalse($colors->isStyleForced());
        $this->assertSame(['none'], $themes['line_number']);
    }
}
