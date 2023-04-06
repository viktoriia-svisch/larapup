<?php
namespace Symfony\Component\Finder\Iterator;
use Symfony\Component\Finder\Glob;
class FilenameFilterIterator extends MultiplePcreFilterIterator
{
    public function accept()
    {
        return $this->isAccepted($this->current()->getFilename());
    }
    protected function toRegex($str)
    {
        return $this->isRegex($str) ? $str : Glob::toRegex($str);
    }
}
