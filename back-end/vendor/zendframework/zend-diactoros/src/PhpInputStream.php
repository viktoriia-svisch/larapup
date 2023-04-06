<?php
namespace Zend\Diactoros;
use function stream_get_contents;
class PhpInputStream extends Stream
{
    private $cache = '';
    private $reachedEof = false;
    public function __construct($stream = 'php:
    {
        parent::__construct($stream, 'r');
    }
    public function __toString()
    {
        if ($this->reachedEof) {
            return $this->cache;
        }
        $this->getContents();
        return $this->cache;
    }
    public function isWritable()
    {
        return false;
    }
    public function read($length)
    {
        $content = parent::read($length);
        if (! $this->reachedEof) {
            $this->cache .= $content;
        }
        if ($this->eof()) {
            $this->reachedEof = true;
        }
        return $content;
    }
    public function getContents($maxLength = -1)
    {
        if ($this->reachedEof) {
            return $this->cache;
        }
        $contents     = stream_get_contents($this->resource, $maxLength);
        $this->cache .= $contents;
        if ($maxLength === -1 || $this->eof()) {
            $this->reachedEof = true;
        }
        return $contents;
    }
}
