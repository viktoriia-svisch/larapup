<?php
namespace Illuminate\Database;
class ConnectionResolver implements ConnectionResolverInterface
{
    protected $connections = [];
    protected $default;
    public function __construct(array $connections = [])
    {
        foreach ($connections as $name => $connection) {
            $this->addConnection($name, $connection);
        }
    }
    public function connection($name = null)
    {
        if (is_null($name)) {
            $name = $this->getDefaultConnection();
        }
        return $this->connections[$name];
    }
    public function addConnection($name, ConnectionInterface $connection)
    {
        $this->connections[$name] = $connection;
    }
    public function hasConnection($name)
    {
        return isset($this->connections[$name]);
    }
    public function getDefaultConnection()
    {
        return $this->default;
    }
    public function setDefaultConnection($name)
    {
        $this->default = $name;
    }
}
