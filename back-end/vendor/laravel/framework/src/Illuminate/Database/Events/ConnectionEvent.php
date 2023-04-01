<?php
namespace Illuminate\Database\Events;
abstract class ConnectionEvent
{
    public $connectionName;
    public $connection;
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
    }
}
