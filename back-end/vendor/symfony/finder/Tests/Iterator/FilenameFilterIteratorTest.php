<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\FilenameFilterIterator;
class FilenameFilterIteratorTest extends IteratorTestCase
{
    public function testAccept($matchPatterns, $noMatchPatterns, $expected)
    {
        $inner = new InnerNameIterator(['test.php', 'test.py', 'foo.php']);
        $iterator = new FilenameFilterIterator($inner, $matchPatterns, $noMatchPatterns);
        $this->assertIterator($expected, $iterator);
    }
    public function getAcceptData()
    {
        return [
            [['test.*'], [], ['test.php', 'test.py']],
            [[], ['test.*'], ['foo.php']],
            [['*.php'], ['test.*'], ['foo.php']],
            [['*.php', '*.py'], ['foo.*'], ['test.php', 'test.py']],
            [['/\.php$/'], [], ['test.php', 'foo.php']],
            [[], ['/\.php$/'], ['test.py']],
        ];
    }
}
class InnerNameIterator extends \ArrayIterator
{
    public function current()
    {
        return new \SplFileInfo(parent::current());
    }
    public function getFilename()
    {
        return parent::current();
    }
}
