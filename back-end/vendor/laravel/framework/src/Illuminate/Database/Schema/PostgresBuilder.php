<?php
namespace Illuminate\Database\Schema;
class PostgresBuilder extends Builder
{
    public function hasTable($table)
    {
        [$schema, $table] = $this->parseSchemaAndTable($table);
        $table = $this->connection->getTablePrefix().$table;
        return count($this->connection->select(
            $this->grammar->compileTableExists(), [$schema, $table]
        )) > 0;
    }
    public function dropAllTables()
    {
        $tables = [];
        $excludedTables = ['spatial_ref_sys'];
        foreach ($this->getAllTables() as $row) {
            $row = (array) $row;
            $table = reset($row);
            if (! in_array($table, $excludedTables)) {
                $tables[] = $table;
            }
        }
        if (empty($tables)) {
            return;
        }
        $this->connection->statement(
            $this->grammar->compileDropAllTables($tables)
        );
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
            $this->grammar->compileGetAllTables($this->connection->getConfig('schema'))
        );
    }
    protected function getAllViews()
    {
        return $this->connection->select(
            $this->grammar->compileGetAllViews($this->connection->getConfig('schema'))
        );
    }
    public function getColumnListing($table)
    {
        [$schema, $table] = $this->parseSchemaAndTable($table);
        $table = $this->connection->getTablePrefix().$table;
        $results = $this->connection->select(
            $this->grammar->compileColumnListing(), [$schema, $table]
        );
        return $this->connection->getPostProcessor()->processColumnListing($results);
    }
    protected function parseSchemaAndTable($table)
    {
        $table = explode('.', $table);
        if (is_array($schema = $this->connection->getConfig('schema'))) {
            if (in_array($table[0], $schema)) {
                return [array_shift($table), implode('.', $table)];
            }
            $schema = head($schema);
        }
        return [$schema ?: 'public', implode('.', $table)];
    }
}
