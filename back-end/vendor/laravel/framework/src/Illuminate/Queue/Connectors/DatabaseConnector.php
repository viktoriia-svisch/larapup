<?php
namespace Illuminate\Queue\Connectors;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Database\ConnectionResolverInterface;
class DatabaseConnector implements ConnectorInterface
{
    protected $connections;
    public function __construct(ConnectionResolverInterface $connections)
    {
        $this->connections = $connections;
    }
    public function connect(array $config)
    {
        return new DatabaseQueue(
            $this->connections->connection($config['connection'] ?? null),
            $config['table'],
            $config['queue'],
            $config['retry_after'] ?? 60
        );
    }
}
