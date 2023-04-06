<?php
namespace Nexmo\Client\Request;
use Nexmo\Client\Response\ResponseInterface;
interface WrapResponseInterface
{
    public function wrapResponse(ResponseInterface $response);
}
