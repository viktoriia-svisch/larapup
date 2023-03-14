<?php
namespace Nexmo\Verify;
class Check
{
    const VALID = 'VALID';
    const INVALID = 'INVALID';
    protected $data;
    public function __construct(Array $data)
    {
        $this->data = $data;
    }
    public function getCode()
    {
        return $this->data['code'];
    }
    public function getDate()
    {
        return new \DateTime($this->data['date_received']);
    }
    public function getStatus()
    {
        return $this->data['status'];
    }
    public function getIpAddress()
    {
        return $this->data['ip_address'];
    }
}
