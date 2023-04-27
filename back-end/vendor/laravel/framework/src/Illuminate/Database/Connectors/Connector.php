<?php
namespace Illuminate\Database\Connectors;
use PDO;
use Exception;
use Throwable;
use Doctrine\DBAL\Driver\PDOConnection;
use Illuminate\Database\DetectsLostConnections;
class Connector
{
    use DetectsLostConnections;
    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    public function createConnection($dsn, array $config, array $options)
    {
        [$username, $password] = [
            $config['username'] ?? null, $config['password'] ?? null,
        ];
        try {
            return $this->createPdoConnection(
                $dsn, $username, $password, $options
            );
        } catch (Exception $e) {
            return $this->tryAgainIfCausedByLostConnection(
                $e, $dsn, $username, $password, $options
            );
        }
    }
    protected function createPdoConnection($dsn, $username, $password, $options)
    {
        if (class_exists(PDOConnection::class) && ! $this->isPersistentConnection($options)) {
            return new PDOConnection($dsn, $username, $password, $options);
        }
        return new PDO($dsn, $username, $password, $options);
    }
    protected function isPersistentConnection($options)
    {
        return isset($options[PDO::ATTR_PERSISTENT]) &&
               $options[PDO::ATTR_PERSISTENT];
    }
    protected function tryAgainIfCausedByLostConnection(Throwable $e, $dsn, $username, $password, $options)
    {
        if ($this->causedByLostConnection($e)) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }
        throw $e;
    }
    public function getOptions(array $config)
    {
        $options = $config['options'] ?? [];
        return array_diff_key($this->options, $options) + $options;
    }
    public function getDefaultOptions()
    {
        return $this->options;
    }
    public function setDefaultOptions(array $options)
    {
        $this->options = $options;
    }
}
