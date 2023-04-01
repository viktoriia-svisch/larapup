<?php
namespace Illuminate\Mail\Events;
class MessageSent
{
    public $message;
    public $data;
    public function __construct($message, $data = [])
    {
        $this->data = $data;
        $this->message = $message;
    }
}
