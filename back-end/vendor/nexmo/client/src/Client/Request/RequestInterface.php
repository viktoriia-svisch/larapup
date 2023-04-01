<?php
namespace Nexmo\Client\Request;
interface RequestInterface
{
    public function getParams();
    public function getURI();
}
