<?php
namespace Illuminate\Database\Schema\Grammars;
use RuntimeException;
use Illuminate\Support\Fluent;
use Doctrine\DBAL\Schema\TableDiff;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Grammar as BaseGrammar;
use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
abstract class Grammar extends BaseGrammar
{
    protected $transactions = false;
    protected $fluentCommands = [];
    public function compileRenameColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return RenameColumn::compile($this, $blueprint, $command, $connection);
    }
    public function compileChange(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        return ChangeColumn::compile($this, $blueprint, $command, $connection);
    }
    public function compileForeign(Blueprint $blueprint, Fluent $command)
    {
        $sql = sprintf('alter table %s add constraint %s ',
            $this->wrapTable($blueprint),
            $this->wrap($command->index)
        );
        $sql .= sprintf('foreign key (%s) references %s (%s)',
            $this->columnize($command->columns),
            $this->wrapTable($command->on),
            $this->columnize((array) $command->references)
        );
        if (! is_null($command->onDelete)) {
            $sql .= " on delete {$command->onDelete}";
        }
        if (! is_null($command->onUpdate)) {
            $sql .= " on update {$command->onUpdate}";
        }
        return $sql;
    }
    protected function getColumns(Blueprint $blueprint)
    {
        $columns = [];
        foreach ($blueprint->getAddedColumns() as $column) {
            $sql = $this->wrap($column).' '.$this->getType($column);
            $columns[] = $this->addModifiers($sql, $blueprint, $column);
        }
        return $columns;
    }
    protected function getType(Fluent $column)
    {
        return $this->{'type'.ucfirst($column->type)}($column);
    }
    protected function typeComputed(Fluent $column)
    {
        throw new RuntimeException('This database driver does not support the computed type.');
    }
    protected function addModifiers($sql, Blueprint $blueprint, Fluent $column)
    {
        foreach ($this->modifiers as $modifier) {
            if (method_exists($this, $method = "modify{$modifier}")) {
                $sql .= $this->{$method}($blueprint, $column);
            }
        }
        return $sql;
    }
    protected function getCommandByName(Blueprint $blueprint, $name)
    {
        $commands = $this->getCommandsByName($blueprint, $name);
        if (count($commands) > 0) {
            return reset($commands);
        }
    }
    protected function getCommandsByName(Blueprint $blueprint, $name)
    {
        return array_filter($blueprint->getCommands(), function ($value) use ($name) {
            return $value->name == $name;
        });
    }
    public function prefixArray($prefix, array $values)
    {
        return array_map(function ($value) use ($prefix) {
            return $prefix.' '.$value;
        }, $values);
    }
    public function wrapTable($table)
    {
        return parent::wrapTable(
            $table instanceof Blueprint ? $table->getTable() : $table
        );
    }
    public function wrap($value, $prefixAlias = false)
    {
        return parent::wrap(
            $value instanceof Fluent ? $value->name : $value, $prefixAlias
        );
    }
    protected function getDefaultValue($value)
    {
        if ($value instanceof Expression) {
            return $value;
        }
        return is_bool($value)
                    ? "'".(int) $value."'"
                    : "'".(string) $value."'";
    }
    public function getDoctrineTableDiff(Blueprint $blueprint, SchemaManager $schema)
    {
        $table = $this->getTablePrefix().$blueprint->getTable();
        return tap(new TableDiff($table), function ($tableDiff) use ($schema, $table) {
            $tableDiff->fromTable = $schema->listTableDetails($table);
        });
    }
    public function getFluentCommands()
    {
        return $this->fluentCommands;
    }
    public function supportsSchemaTransactions()
    {
        return $this->transactions;
    }
}
