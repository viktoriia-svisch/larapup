<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
class RecursiveDirectoryIteratorTest extends IteratorTestCase
{
    public function testRewindOnFtp()
    {
        try {
            $i = new RecursiveDirectoryIterator('ftp:
        } catch (\UnexpectedValueException $e) {
            $this->markTestSkipped('Unsupported stream "ftp".');
        }
        $i->rewind();
        $this->assertTrue(true);
    }
    public function testSeekOnFtp()
    {
        try {
            $i = new RecursiveDirectoryIterator('ftp:
        } catch (\UnexpectedValueException $e) {
            $this->markTestSkipped('Unsupported stream "ftp".');
        }
        $contains = [
            'ftp:
            'ftp:
        ];
        $actual = [];
        $i->seek(0);
        $actual[] = $i->getPathname();
        $i->seek(1);
        $actual[] = $i->getPathname();
        $this->assertEquals($contains, $actual);
    }
}
