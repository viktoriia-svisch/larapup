<?php
namespace Illuminate\Notifications\Messages;
use Illuminate\Bus\Queueable;
class BroadcastMessage
{
    use Queueable;
    public $data;
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    public function data($data)
    {
        $this->data = $data;
        return $this;
    }
}
