<?php
namespace SebastianBergmann\FileIterator;
use PHPUnit\Framework\TestCase;
class FactoryTest extends TestCase
{
    private $root;
    private $factory;
    protected function setUp(): void
    {
        $this->root    = __DIR__;
        $this->factory = new Factory;
    }
    public function testFindFilesInTestDirectory(): void
    {
        $iterator = $this->factory->getFileIterator($this->root, 'Test.php');
        $files    = \iterator_to_array($iterator);
        $this->assertGreaterThanOrEqual(1, \count($files));
    }
    public function testFindFilesWithExcludedNonExistingSubdirectory(): void
    {
        $iterator = $this->factory->getFileIterator($this->root, 'Test.php', '', [$this->root . '/nonExistingDir']);
        $files    = \iterator_to_array($iterator);
        $this->assertGreaterThanOrEqual(1, \count($files));
    }
}
