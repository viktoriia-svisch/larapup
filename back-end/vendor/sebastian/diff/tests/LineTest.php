<?php declare(strict_types=1);
namespace SebastianBergmann\Diff;
use PHPUnit\Framework\TestCase;
final class LineTest extends TestCase
{
    private $line;
    protected function setUp(): void
    {
        $this->line = new Line;
    }
    public function testCanBeCreatedWithoutArguments(): void
    {
        $this->assertInstanceOf(Line::class, $this->line);
    }
    public function testTypeCanBeRetrieved(): void
    {
        $this->assertSame(Line::UNCHANGED, $this->line->getType());
    }
    public function testContentCanBeRetrieved(): void
    {
        $this->assertSame('', $this->line->getContent());
    }
}
