<?php
namespace Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
class RequestException extends TransferException
{
    private $request;
    public function __construct($message, RequestInterface $request, \Exception $previous = null)
    {
        $this->request = $request;
        parent::__construct($message, 0, $previous);
    }
    public function getRequest()
    {
        return $this->request;
    }
}
