<?php
namespace Illuminate\Database\Schema;
class MySqlBuilder extends Builder
{
    public function hasTable($table)
    {
        $table = $this->connection->getTablePrefix().$table;
        return count($this->connection->select(
            $this->grammar->compileTableExists(), [$this->connection->getDatabaseName(), $table]
        )) > 0;
    }
    public function getColumnListing($table)
    {
        $table = $this->connection->getTablePrefix().$table;
        $results = $this->connection->select(
            $this->grammar->compileColumnListing(), [$this->connection->getDatabaseName(), $table]
        );
        return $this->connection->getPostProcessor()->processColumnListing($results);
    }
    public function dropAllTables()
    {
        $tables = [];
        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;
            $tables[] = reset($row);
        }
        if (empty($tables)) {
            return;
        }
        $this->disableForeignKeyConstraints();
        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );
        $this->enableForeignKeyConstraints();
    }
    public function dropAllViews()
    {
        $views = [];
        foreach ($this->getAllViews() as $row) {
            $row = (array) $row;
            $views[] = reset($row);
        }
        if (empty($views)) {
            return;
        }
        $this->connection->statement(
            $this->grammar->compileDropAllViews($views)
        );
    }
    protected function getAllTables()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllTables()
        );
    }
    protected function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews()
        );
    }
}
