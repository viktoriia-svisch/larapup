<?php
namespace Symfony\Component\HttpFoundation\Tests\File;
use Symfony\Component\HttpFoundation\File\File as OrigFile;
class FakeFile extends OrigFile
{
    private $realpath;
    public function __construct($realpath, $path)
    {
        $this->realpath = $realpath;
        parent::__construct($path, false);
    }
    public function isReadable()
    {
        return true;
    }
    public function getRealpath()
    {
        return $this->realpath;
    }
    public function getSize()
    {
        return 42;
    }
    public function getMTime()
    {
        return time();
    }
}
