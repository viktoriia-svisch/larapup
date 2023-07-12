<?php
namespace Nexmo\Client\Response;
interface ResponseInterface
{
    public function getData();
    public function isError();
    public function isSuccess();
}
