<?php
namespace Illuminate\Broadcasting\Broadcasters;
use Psr\Log\LoggerInterface;
class LogBroadcaster extends Broadcaster
{
    protected $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    public function auth($request)
    {
    }
    public function validAuthenticationResponse($request, $result)
    {
    }
    public function broadcast(array $channels, $event, array $payload = [])
    {
        $channels = implode(', ', $this->formatChannels($channels));
        $payload = json_encode($payload, JSON_PRETTY_PRINT);
        $this->logger->info('Broadcasting ['.$event.'] on channels ['.$channels.'] with payload:'.PHP_EOL.$payload);
    }
}
