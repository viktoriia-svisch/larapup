<?php
namespace Http\Client\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
class HttpException extends RequestException
{
    protected $response;
    public function __construct(
        $message,
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $previous = null
    ) {
        parent::__construct($message, $request, $previous);
        $this->response = $response;
        $this->code = $response->getStatusCode();
    }
    public function getResponse()
    {
        return $this->response;
    }
    public static function create(
        RequestInterface $request,
        ResponseInterface $response,
        \Exception $previous = null
    ) {
        $message = sprintf(
            '[url] %s [http method] %s [status code] %s [reason phrase] %s',
            $request->getRequestTarget(),
            $request->getMethod(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        return new self($message, $request, $response, $previous);
    }
}
