<?php
namespace Illuminate\Database;
use Illuminate\Database\Schema\PostgresBuilder;
use Doctrine\DBAL\Driver\PDOPgSql\Driver as DoctrineDriver;
use Illuminate\Database\Query\Processors\PostgresProcessor;
use Illuminate\Database\Query\Grammars\PostgresGrammar as QueryGrammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar as SchemaGrammar;
class PostgresConnection extends Connection
{
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new PostgresBuilder($this);
    }
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }
    protected function getDefaultPostProcessor()
    {
        return new PostgresProcessor;
    }
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}
