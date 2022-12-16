<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class ExceptionDataCollector extends DataCollector
{
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        if (null !== $exception) {
            $this->data = [
                'exception' => FlattenException::create($exception),
            ];
        }
    }
    public function reset()
    {
        $this->data = [];
    }
    public function hasException()
    {
        return isset($this->data['exception']);
    }
    public function getException()
    {
        return $this->data['exception'];
    }
    public function getMessage()
    {
        return $this->data['exception']->getMessage();
    }
    public function getCode()
    {
        return $this->data['exception']->getCode();
    }
    public function getStatusCode()
    {
        return $this->data['exception']->getStatusCode();
    }
    public function getTrace()
    {
        return $this->data['exception']->getTrace();
    }
    public function getName()
    {
        return 'exception';
    }
}
