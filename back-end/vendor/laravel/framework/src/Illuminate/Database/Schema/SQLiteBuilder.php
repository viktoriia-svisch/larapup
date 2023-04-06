<?php
namespace Illuminate\Database\Schema;
class SQLiteBuilder extends Builder
{
    public function dropAllTables()
    {
        if ($this->connection->getDatabaseName() !== ':memory:') {
            return $this->refreshDatabaseFile();
        }
        $this->connection->select($this->grammar->compileEnableWriteableSchema());
        $this->connection->select($this->grammar->compileDropAllTables());
        $this->connection->select($this->grammar->compileDisableWriteableSchema());
        $this->connection->select($this->grammar->compileRebuild());
    }
    public function dropAllViews()
    {
        $this->connection->select($this->grammar->compileEnableWriteableSchema());
        $this->connection->select($this->grammar->compileDropAllViews());
        $this->connection->select($this->grammar->compileDisableWriteableSchema());
        $this->connection->select($this->grammar->compileRebuild());
    }
    public function refreshDatabaseFile()
    {
        file_put_contents($this->connection->getDatabaseName(), '');
    }
}
