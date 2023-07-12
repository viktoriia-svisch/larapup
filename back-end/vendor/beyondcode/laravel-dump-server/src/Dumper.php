<?php
namespace BeyondCode\DumpServer;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Server\Connection;
class Dumper
{
    private $connection;
    public function __construct(Connection $connection = null)
    {
        $this->connection = $connection;
    }
    public function dump($value)
    {
        if (class_exists(CliDumper::class)) {
            $data = (new VarCloner)->cloneVar($value);
            if ($this->connection === null || $this->connection->write($data) === false) {
                $dumper = in_array(PHP_SAPI, ['cli', 'phpdbg']) ? new CliDumper : new HtmlDumper;
                $dumper->dump($data);
            }
        } else {
            var_dump($value);
        }
    }
}
