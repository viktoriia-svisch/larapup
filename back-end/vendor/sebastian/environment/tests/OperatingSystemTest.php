<?php declare(strict_types=1);
namespace SebastianBergmann\Environment;
use PHPUnit\Framework\TestCase;
final class OperatingSystemTest extends TestCase
{
    private $os;
    protected function setUp(): void
    {
        $this->os = new OperatingSystem;
    }
    public function testFamilyCanBeRetrieved(): void
    {
        $this->assertEquals('Linux', $this->os->getFamily());
    }
}
