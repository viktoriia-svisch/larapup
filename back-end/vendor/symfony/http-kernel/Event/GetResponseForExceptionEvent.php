<?php
namespace Symfony\Component\HttpKernel\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
class GetResponseForExceptionEvent extends GetResponseEvent
{
    private $exception;
    private $allowCustomResponseCode = false;
    public function __construct(HttpKernelInterface $kernel, Request $request, int $requestType, \Exception $e)
    {
        parent::__construct($kernel, $request, $requestType);
        $this->setException($e);
    }
    public function getException()
    {
        return $this->exception;
    }
    public function setException(\Exception $exception)
    {
        $this->exception = $exception;
    }
    public function allowCustomResponseCode()
    {
        $this->allowCustomResponseCode = true;
    }
    public function isAllowingCustomResponseCode()
    {
        return $this->allowCustomResponseCode;
    }
}
