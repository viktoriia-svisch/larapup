<?php
namespace Illuminate\Broadcasting;
use Illuminate\Support\Facades\Broadcast;
trait InteractsWithSockets
{
    public $socket;
    public function dontBroadcastToCurrentUser()
    {
        $this->socket = Broadcast::socket();
        return $this;
    }
    public function broadcastToEveryone()
    {
        $this->socket = null;
        return $this;
    }
}
