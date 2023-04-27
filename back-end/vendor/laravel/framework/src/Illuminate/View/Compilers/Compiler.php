<?php
namespace Illuminate\View\Compilers;
use InvalidArgumentException;
use Illuminate\Filesystem\Filesystem;
abstract class Compiler
{
    protected $files;
    protected $cachePath;
    public function __construct(Filesystem $files, $cachePath)
    {
        if (! $cachePath) {
            throw new InvalidArgumentException('Please provide a valid cache path.');
        }
        $this->files = $files;
        $this->cachePath = $cachePath;
    }
    public function getCompiledPath($path)
    {
        return $this->cachePath.'/'.sha1($path).'.php';
    }
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);
        if (! $this->files->exists($compiled)) {
            return true;
        }
        return $this->files->lastModified($path) >=
               $this->files->lastModified($compiled);
    }
}
