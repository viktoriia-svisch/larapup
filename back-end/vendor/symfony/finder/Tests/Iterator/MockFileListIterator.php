<?php
namespace Symfony\Component\Finder\Tests\Iterator;
class MockFileListIterator extends \ArrayIterator
{
    public function __construct(array $filesArray = [])
    {
        $files = array_map(function ($file) { return new MockSplFileInfo($file); }, $filesArray);
        parent::__construct($files);
    }
}
