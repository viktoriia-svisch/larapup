<?php
namespace Illuminate\Mail\Events;
class MessageSending
{
    public $message;
    public $data;
    public function __construct($message, $data = [])
    {
        $this->data = $data;
        $this->message = $message;
    }
}
