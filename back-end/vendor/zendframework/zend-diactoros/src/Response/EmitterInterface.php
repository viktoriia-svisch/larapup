<?php
namespace Zend\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
interface EmitterInterface
{
    public function emit(ResponseInterface $response);
}
