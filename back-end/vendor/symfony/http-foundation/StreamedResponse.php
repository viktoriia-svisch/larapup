<?php
namespace Symfony\Component\HttpFoundation;
class StreamedResponse extends Response
{
    protected $callback;
    protected $streamed;
    private $headersSent;
    public function __construct(callable $callback = null, int $status = 200, array $headers = [])
    {
        parent::__construct(null, $status, $headers);
        if (null !== $callback) {
            $this->setCallback($callback);
        }
        $this->streamed = false;
        $this->headersSent = false;
    }
    public static function create($callback = null, $status = 200, $headers = [])
    {
        return new static($callback, $status, $headers);
    }
    public function setCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }
    public function sendHeaders()
    {
        if ($this->headersSent) {
            return $this;
        }
        $this->headersSent = true;
        return parent::sendHeaders();
    }
    public function sendContent()
    {
        if ($this->streamed) {
            return $this;
        }
        $this->streamed = true;
        if (null === $this->callback) {
            throw new \LogicException('The Response callback must not be null.');
        }
        ($this->callback)();
        return $this;
    }
    public function setContent($content)
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a StreamedResponse instance.');
        }
        $this->streamed = true;
        return $this;
    }
    public function getContent()
    {
        return false;
    }
}
