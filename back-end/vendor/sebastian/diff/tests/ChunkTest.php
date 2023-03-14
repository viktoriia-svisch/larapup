<?php declare(strict_types=1);
namespace SebastianBergmann\Diff;
use PHPUnit\Framework\TestCase;
final class ChunkTest extends TestCase
{
    private $chunk;
    protected function setUp(): void
    {
        $this->chunk = new Chunk;
    }
    public function testHasInitiallyNoLines(): void
    {
        $this->assertSame([], $this->chunk->getLines());
    }
    public function testCanBeCreatedWithoutArguments(): void
    {
        $this->assertInstanceOf(Chunk::class, $this->chunk);
    }
    public function testStartCanBeRetrieved(): void
    {
        $this->assertSame(0, $this->chunk->getStart());
    }
    public function testStartRangeCanBeRetrieved(): void
    {
        $this->assertSame(1, $this->chunk->getStartRange());
    }
    public function testEndCanBeRetrieved(): void
    {
        $this->assertSame(0, $this->chunk->getEnd());
    }
    public function testEndRangeCanBeRetrieved(): void
    {
        $this->assertSame(1, $this->chunk->getEndRange());
    }
    public function testLinesCanBeRetrieved(): void
    {
        $this->assertSame([], $this->chunk->getLines());
    }
    public function testLinesCanBeSet(): void
    {
        $lines = [new Line(Line::ADDED, 'added'), new Line(Line::REMOVED, 'removed')];
        $this->chunk->setLines($lines);
        $this->assertSame($lines, $this->chunk->getLines());
    }
}
