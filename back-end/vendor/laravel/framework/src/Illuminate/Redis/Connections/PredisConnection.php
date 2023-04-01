<?php
namespace Illuminate\Redis\Connections;
use Closure;
use Illuminate\Contracts\Redis\Connection as ConnectionContract;
class PredisConnection extends Connection implements ConnectionContract
{
    public function __construct($client)
    {
        $this->client = $client;
    }
    public function createSubscription($channels, Closure $callback, $method = 'subscribe')
    {
        $loop = $this->pubSubLoop();
        call_user_func_array([$loop, $method], (array) $channels);
        foreach ($loop as $message) {
            if ($message->kind === 'message' || $message->kind === 'pmessage') {
                call_user_func($callback, $message->payload, $message->channel);
            }
        }
        unset($loop);
    }
}
