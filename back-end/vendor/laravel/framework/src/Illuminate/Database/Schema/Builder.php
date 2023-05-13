<?php
namespace Illuminate\Database\Schema;
use Closure;
use LogicException;
use Illuminate\Database\Connection;
class Builder
{
    protected $connection;
    protected $grammar;
    protected $resolver;
    public static $defaultStringLength = 255;
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->grammar = $connection->getSchemaGrammar();
    }
    public static function defaultStringLength($length)
    {
        static::$defaultStringLength = $length;
    }
    public function hasTable($table)
    {
        $table = $this->connection->getTablePrefix().$table;
        return count($this->connection->selectFromWriteConnection(
            $this->grammar->compileTableExists(), [$table]
        )) > 0;
    }
    public function hasColumn($table, $column)
    {
        return in_array(
            strtolower($column), array_map('strtolower', $this->getColumnListing($table))
        );
    }
    public function hasColumns($table, array $columns)
    {
        $tableColumns = array_map('strtolower', $this->getColumnListing($table));
        foreach ($columns as $column) {
            if (! in_array(strtolower($column), $tableColumns)) {
                return false;
            }
        }
        return true;
    }
    public function getColumnType($table, $column)
    {
        $table = $this->connection->getTablePrefix().$table;
        return $this->connection->getDoctrineColumn($table, $column)->getType()->getName();
    }
    public function getColumnListing($table)
    {
        $results = $this->connection->selectFromWriteConnection($this->grammar->compileColumnListing(
            $this->connection->getTablePrefix().$table
        ));
        return $this->connection->getPostProcessor()->processColumnListing($results);
    }
    public function table($table, Closure $callback)
    {
        $this->build($this->createBlueprint($table, $callback));
    }
    public function create($table, Closure $callback)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) use ($callback) {
            $blueprint->create();
            $callback($blueprint);
        }));
    }
    public function drop($table)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->drop();
        }));
    }
    public function dropIfExists($table)
    {
        $this->build(tap($this->createBlueprint($table), function ($blueprint) {
            $blueprint->dropIfExists();
        }));
    }
    public function dropAllTables()
    {
        throw new LogicException('This database driver does not support dropping all tables.');
    }
    public function dropAllViews()
    {
        throw new LogicException('This database driver does not support dropping all views.');
    }
    public function rename($from, $to)
    {
        $this->build(tap($this->createBlueprint($from), function ($blueprint) use ($to) {
            $blueprint->rename($to);
        }));
    }
    public function enableForeignKeyConstraints()
    {
        return $this->connection->statement(
            $this->grammar->compileEnableForeignKeyConstraints()
        );
    }
    public function disableForeignKeyConstraints()
    {
        return $this->connection->statement(
            $this->grammar->compileDisableForeignKeyConstraints()
        );
    }
    protected function build(Blueprint $blueprint)
    {
        $blueprint->build($this->connection, $this->grammar);
    }
    protected function createBlueprint($table, Closure $callback = null)
    {
        $prefix = $this->connection->getConfig('prefix_indexes')
                    ? $this->connection->getConfig('prefix')
                    : '';
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $table, $callback, $prefix);
        }
        return new Blueprint($table, $callback, $prefix);
    }
    public function getConnection()
    {
        return $this->connection;
    }
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }
    public function blueprintResolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }
}
