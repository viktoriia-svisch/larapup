<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\CustomFilterIterator;
class CustomFilterIteratorTest extends IteratorTestCase
{
    public function testWithInvalidFilter()
    {
        new CustomFilterIterator(new Iterator(), ['foo']);
    }
    public function testAccept($filters, $expected)
    {
        $inner = new Iterator(['test.php', 'test.py', 'foo.php']);
        $iterator = new CustomFilterIterator($inner, $filters);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        return [
            [[function (\SplFileInfo $fileinfo) { return false; }], []],
            [[function (\SplFileInfo $fileinfo) { return 0 === strpos($fileinfo, 'test'); }], ['test.php', 'test.py']],
            [['is_dir'], []],
        ];
    }
}
