<?php
namespace Nexmo\Client\Response;
abstract class AbstractResponse implements ResponseInterface
{
    protected $data;
    public function getData()
    {
        return $this->data;
    }
    public function isSuccess()
    {
        return isset($this->data['status']) AND $this->data['status'] == 0;
    }
    public function isError()
    {
        return !$this->isSuccess();
    }
}
