<?php
namespace Zend\Diactoros;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use const SEEK_SET;
final class RelativeStream implements StreamInterface
{
    private $decoratedStream;
    private $offset;
    public function __construct(StreamInterface $decoratedStream, $offset)
    {
        $this->decoratedStream = $decoratedStream;
        $this->offset = (int)$offset;
    }
    public function __toString()
    {
        if ($this->isSeekable()) {
            $this->seek(0);
        }
        return $this->getContents();
    }
    public function close()
    {
        $this->decoratedStream->close();
    }
    public function detach()
    {
        return $this->decoratedStream->detach();
    }
    public function getSize()
    {
        return $this->decoratedStream->getSize() - $this->offset;
    }
    public function tell()
    {
        return $this->decoratedStream->tell() - $this->offset;
    }
    public function eof()
    {
        return $this->decoratedStream->eof();
    }
    public function isSeekable()
    {
        return $this->decoratedStream->isSeekable();
    }
    public function seek($offset, $whence = SEEK_SET)
    {
        if ($whence == SEEK_SET) {
            return $this->decoratedStream->seek($offset + $this->offset, $whence);
        }
        return $this->decoratedStream->seek($offset, $whence);
    }
    public function rewind()
    {
        return $this->seek(0);
    }
    public function isWritable()
    {
        return $this->decoratedStream->isWritable();
    }
    public function write($string)
    {
        if ($this->tell() < 0) {
            throw new RuntimeException('Invalid pointer position');
        }
        return $this->decoratedStream->write($string);
    }
    public function isReadable()
    {
        return $this->decoratedStream->isReadable();
    }
    public function read($length)
    {
        if ($this->tell() < 0) {
            throw new RuntimeException('Invalid pointer position');
        }
        return $this->decoratedStream->read($length);
    }
    public function getContents()
    {
        if ($this->tell() < 0) {
            throw new RuntimeException('Invalid pointer position');
        }
        return $this->decoratedStream->getContents();
    }
    public function getMetadata($key = null)
    {
        return $this->decoratedStream->getMetadata($key);
    }
}
