<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use function array_key_exists;
use const SEEK_SET;
class CallbackStream implements StreamInterface
{
    protected $callback;
    public function __construct(callable $callback)
    {
        $this->attach($callback);
    }
    public function __toString()
    {
        return $this->getContents();
    }
    public function close()
    {
        $this->callback = null;
    }
    public function detach()
    {
        $callback = $this->callback;
        $this->callback = null;
        return $callback;
    }
    public function attach(callable $callback)
    {
        $this->callback = $callback;
    }
    public function getSize()
    {
    }
    public function tell()
    {
        throw new RuntimeException('Callback streams cannot tell position');
    }
    public function eof()
    {
        return empty($this->callback);
    }
    public function isSeekable()
    {
        return false;
    }
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Callback streams cannot seek position');
    }
    public function rewind()
    {
        throw new RuntimeException('Callback streams cannot rewind position');
    }
    public function isWritable()
    {
        return false;
    }
    public function write($string)
    {
        throw new RuntimeException('Callback streams cannot write');
    }
    public function isReadable()
    {
        return false;
    }
    public function read($length)
    {
        throw new RuntimeException('Callback streams cannot read');
    }
    public function getContents()
    {
        $callback = $this->detach();
        return $callback ? $callback() : '';
    }
    public function getMetadata($key = null)
    {
        $metadata = [
            'eof' => $this->eof(),
            'stream_type' => 'callback',
            'seekable' => false
        ];
        if (null === $key) {
            return $metadata;
        }
        if (! array_key_exists($key, $metadata)) {
            return null;
        }
        return $metadata[$key];
    }
}
