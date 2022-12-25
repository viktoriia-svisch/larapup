<?php
namespace Illuminate\Database;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\Query\Processors\SQLiteProcessor;
use Doctrine\DBAL\Driver\PDOSqlite\Driver as DoctrineDriver;
use Illuminate\Database\Query\Grammars\SQLiteGrammar as QueryGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar as SchemaGrammar;
class SQLiteConnection extends Connection
{
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
        $enableForeignKeyConstraints = $this->getForeignKeyConstraintsConfigurationValue();
        if ($enableForeignKeyConstraints === null) {
            return;
        }
        $enableForeignKeyConstraints
            ? $this->getSchemaBuilder()->enableForeignKeyConstraints()
            : $this->getSchemaBuilder()->disableForeignKeyConstraints();
    }
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new SQLiteBuilder($this);
    }
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }
    protected function getDefaultPostProcessor()
    {
        return new SQLiteProcessor;
    }
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
    protected function getForeignKeyConstraintsConfigurationValue()
    {
        return $this->getConfig('foreign_key_constraints');
    }
}
