<?php
namespace Illuminate\Auth\Access;
class Response
{
    protected $message;
    public function __construct($message = null)
    {
        $this->message = $message;
    }
    public function message()
    {
        return $this->message;
    }
    public function __toString()
    {
        return (string) $this->message();
    }
}
