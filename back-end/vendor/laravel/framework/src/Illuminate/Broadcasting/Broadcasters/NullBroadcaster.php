<?php
namespace Illuminate\Broadcasting\Broadcasters;
class NullBroadcaster extends Broadcaster
{
    public function auth($request)
    {
    }
    public function validAuthenticationResponse($request, $result)
    {
    }
    public function broadcast(array $channels, $event, array $payload = [])
    {
    }
}
