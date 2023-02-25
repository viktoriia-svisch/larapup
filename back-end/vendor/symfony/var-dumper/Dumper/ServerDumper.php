<?php
namespace Symfony\Component\VarDumper\Dumper;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Component\VarDumper\Dumper\ContextProvider\ContextProviderInterface;
use Symfony\Component\VarDumper\Server\Connection;
class ServerDumper implements DataDumperInterface
{
    private $connection;
    private $wrappedDumper;
    public function __construct(string $host, DataDumperInterface $wrappedDumper = null, array $contextProviders = [])
    {
        $this->connection = new Connection($host, $contextProviders);
        $this->wrappedDumper = $wrappedDumper;
    }
    public function getContextProviders(): array
    {
        return $this->connection->getContextProviders();
    }
    public function dump(Data $data)
    {
        if (!$this->connection->write($data) && $this->wrappedDumper) {
            $this->wrappedDumper->dump($data);
        }
    }
}
