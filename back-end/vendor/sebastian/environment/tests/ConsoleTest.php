<?php declare(strict_types=1);
namespace SebastianBergmann\Environment;
use PHPUnit\Framework\TestCase;
final class ConsoleTest extends TestCase
{
    private $console;
    protected function setUp(): void
    {
        $this->console = new Console;
    }
    public function testCanDetectIfStdoutIsInteractiveByDefault(): void
    {
        $this->assertIsBool($this->console->isInteractive());
    }
    public function testCanDetectIfFileDescriptorIsInteractive(): void
    {
        $this->assertIsBool($this->console->isInteractive(\STDOUT));
    }
    public function testCanDetectColorSupport(): void
    {
        $this->assertIsBool($this->console->hasColorSupport());
    }
    public function testCanDetectNumberOfColumns(): void
    {
        $this->assertIsInt($this->console->getNumberOfColumns());
    }
}
