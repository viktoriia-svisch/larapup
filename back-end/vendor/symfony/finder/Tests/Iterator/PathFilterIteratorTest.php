<?php
namespace Symfony\Component\Finder\Tests\Iterator;
use Symfony\Component\Finder\Iterator\PathFilterIterator;
class PathFilterIteratorTest extends IteratorTestCase
{
    public function testFilter(\Iterator $inner, array $matchPatterns, array $noMatchPatterns, array $resultArray)
    {
        $iterator = new PathFilterIterator($inner, $matchPatterns, $noMatchPatterns);
        $this->assertIterator($resultArray, $iterator);
    }
    public function getTestFilterData()
    {
        $inner = new MockFileListIterator();
        $inner[] = new MockSplFileInfo([
            'name' => 'abc.dat',
            'relativePathname' => 'A'.\DIRECTORY_SEPARATOR.'B'.\DIRECTORY_SEPARATOR.'C'.\DIRECTORY_SEPARATOR.'abc.dat',
        ]);
        $inner[] = new MockSplFileInfo([
            'name' => 'ab.dat',
            'relativePathname' => 'A'.\DIRECTORY_SEPARATOR.'B'.\DIRECTORY_SEPARATOR.'ab.dat',
        ]);
        $inner[] = new MockSplFileInfo([
            'name' => 'a.dat',
            'relativePathname' => 'A'.\DIRECTORY_SEPARATOR.'a.dat',
        ]);
        $inner[] = new MockSplFileInfo([
            'name' => 'abc.dat.copy',
            'relativePathname' => 'copy'.\DIRECTORY_SEPARATOR.'A'.\DIRECTORY_SEPARATOR.'B'.\DIRECTORY_SEPARATOR.'C'.\DIRECTORY_SEPARATOR.'abc.dat',
        ]);
        $inner[] = new MockSplFileInfo([
            'name' => 'ab.dat.copy',
            'relativePathname' => 'copy'.\DIRECTORY_SEPARATOR.'A'.\DIRECTORY_SEPARATOR.'B'.\DIRECTORY_SEPARATOR.'ab.dat',
        ]);
        $inner[] = new MockSplFileInfo([
            'name' => 'a.dat.copy',
            'relativePathname' => 'copy'.\DIRECTORY_SEPARATOR.'A'.\DIRECTORY_SEPARATOR.'a.dat',
        ]);
        return [
            [$inner, ['/^A/'],       [], ['abc.dat', 'ab.dat', 'a.dat']],
            [$inner, ['/^A\/B/'],    [], ['abc.dat', 'ab.dat']],
            [$inner, ['/^A\/B\/C/'], [], ['abc.dat']],
            [$inner, ['/A\/B\/C/'], [], ['abc.dat', 'abc.dat.copy']],
            [$inner, ['A'],      [], ['abc.dat', 'ab.dat', 'a.dat', 'abc.dat.copy', 'ab.dat.copy', 'a.dat.copy']],
            [$inner, ['A/B'],    [], ['abc.dat', 'ab.dat', 'abc.dat.copy', 'ab.dat.copy']],
            [$inner, ['A/B/C'], [], ['abc.dat', 'abc.dat.copy']],
            [$inner, ['copy/A'],      [], ['abc.dat.copy', 'ab.dat.copy', 'a.dat.copy']],
            [$inner, ['copy/A/B'],    [], ['abc.dat.copy', 'ab.dat.copy']],
            [$inner, ['copy/A/B/C'], [], ['abc.dat.copy']],
        ];
    }
}
