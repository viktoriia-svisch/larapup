<?php
namespace Illuminate\Database\Schema;
use Closure;
use BadMethodCallException;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\Schema\Grammars\Grammar;
class Blueprint
{
    use Macroable;
    protected $table;
    protected $prefix;
    protected $columns = [];
    protected $commands = [];
    public $engine;
    public $charset;
    public $collation;
    public $temporary = false;
    public function __construct($table, Closure $callback = null, $prefix = '')
    {
        $this->table = $table;
        $this->prefix = $prefix;
        if (! is_null($callback)) {
            $callback($this);
        }
    }
    public function build(Connection $connection, Grammar $grammar)
    {
        foreach ($this->toSql($connection, $grammar) as $statement) {
            $connection->statement($statement);
        }
    }
    public function toSql(Connection $connection, Grammar $grammar)
    {
        $this->addImpliedCommands($grammar);
        $statements = [];
        $this->ensureCommandsAreValid($connection);
        foreach ($this->commands as $command) {
            $method = 'compile'.ucfirst($command->name);
            if (method_exists($grammar, $method)) {
                if (! is_null($sql = $grammar->$method($this, $command, $connection))) {
                    $statements = array_merge($statements, (array) $sql);
                }
            }
        }
        return $statements;
    }
    protected function ensureCommandsAreValid(Connection $connection)
    {
        if ($connection instanceof SQLiteConnection) {
            if ($this->commandsNamed(['dropColumn', 'renameColumn'])->count() > 1) {
                throw new BadMethodCallException(
                    "SQLite doesn't support multiple calls to dropColumn / renameColumn in a single modification."
                );
            }
            if ($this->commandsNamed(['dropForeign'])->count() > 0) {
                throw new BadMethodCallException(
                    "SQLite doesn't support dropping foreign keys (you would need to re-create the table)."
                );
            }
        }
    }
    protected function commandsNamed(array $names)
    {
        return collect($this->commands)->filter(function ($command) use ($names) {
            return in_array($command->name, $names);
        });
    }
    protected function addImpliedCommands(Grammar $grammar)
    {
        if (count($this->getAddedColumns()) > 0 && ! $this->creating()) {
            array_unshift($this->commands, $this->createCommand('add'));
        }
        if (count($this->getChangedColumns()) > 0 && ! $this->creating()) {
            array_unshift($this->commands, $this->createCommand('change'));
        }
        $this->addFluentIndexes();
        $this->addFluentCommands($grammar);
    }
    protected function addFluentIndexes()
    {
        foreach ($this->columns as $column) {
            foreach (['primary', 'unique', 'index', 'spatialIndex'] as $index) {
                if ($column->{$index} === true) {
                    $this->{$index}($column->name);
                    continue 2;
                }
                elseif (isset($column->{$index})) {
                    $this->{$index}($column->name, $column->{$index});
                    continue 2;
                }
            }
        }
    }
    public function addFluentCommands(Grammar $grammar)
    {
        foreach ($this->columns as $column) {
            foreach ($grammar->getFluentCommands() as $commandName) {
                $attributeName = lcfirst($commandName);
                if (! isset($column->{$attributeName})) {
                    continue;
                }
                $value = $column->{$attributeName};
                $this->addCommand(
                    $commandName, compact('value', 'column')
                );
            }
        }
    }
    protected function creating()
    {
        return collect($this->commands)->contains(function ($command) {
            return $command->name === 'create';
        });
    }
    public function create()
    {
        return $this->addCommand('create');
    }
    public function temporary()
    {
        $this->temporary = true;
    }
    public function drop()
    {
        return $this->addCommand('drop');
    }
    public function dropIfExists()
    {
        return $this->addCommand('dropIfExists');
    }
    public function dropColumn($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        return $this->addCommand('dropColumn', compact('columns'));
    }
    public function renameColumn($from, $to)
    {
        return $this->addCommand('renameColumn', compact('from', 'to'));
    }
    public function dropPrimary($index = null)
    {
        return $this->dropIndexCommand('dropPrimary', 'primary', $index);
    }
    public function dropUnique($index)
    {
        return $this->dropIndexCommand('dropUnique', 'unique', $index);
    }
    public function dropIndex($index)
    {
        return $this->dropIndexCommand('dropIndex', 'index', $index);
    }
    public function dropSpatialIndex($index)
    {
        return $this->dropIndexCommand('dropSpatialIndex', 'spatialIndex', $index);
    }
    public function dropForeign($index)
    {
        return $this->dropIndexCommand('dropForeign', 'foreign', $index);
    }
    public function renameIndex($from, $to)
    {
        return $this->addCommand('renameIndex', compact('from', 'to'));
    }
    public function dropTimestamps()
    {
        $this->dropColumn('created_at', 'updated_at');
    }
    public function dropTimestampsTz()
    {
        $this->dropTimestamps();
    }
    public function dropSoftDeletes($column = 'deleted_at')
    {
        $this->dropColumn($column);
    }
    public function dropSoftDeletesTz($column = 'deleted_at')
    {
        $this->dropSoftDeletes($column);
    }
    public function dropRememberToken()
    {
        $this->dropColumn('remember_token');
    }
    public function dropMorphs($name, $indexName = null)
    {
        $this->dropIndex($indexName ?: $this->createIndexName('index', ["{$name}_type", "{$name}_id"]));
        $this->dropColumn("{$name}_type", "{$name}_id");
    }
    public function rename($to)
    {
        return $this->addCommand('rename', compact('to'));
    }
    public function primary($columns, $name = null, $algorithm = null)
    {
        return $this->indexCommand('primary', $columns, $name, $algorithm);
    }
    public function unique($columns, $name = null, $algorithm = null)
    {
        return $this->indexCommand('unique', $columns, $name, $algorithm);
    }
    public function index($columns, $name = null, $algorithm = null)
    {
        return $this->indexCommand('index', $columns, $name, $algorithm);
    }
    public function spatialIndex($columns, $name = null)
    {
        return $this->indexCommand('spatialIndex', $columns, $name);
    }
    public function foreign($columns, $name = null)
    {
        return $this->indexCommand('foreign', $columns, $name);
    }
    public function increments($column)
    {
        return $this->unsignedInteger($column, true);
    }
    public function tinyIncrements($column)
    {
        return $this->unsignedTinyInteger($column, true);
    }
    public function smallIncrements($column)
    {
        return $this->unsignedSmallInteger($column, true);
    }
    public function mediumIncrements($column)
    {
        return $this->unsignedMediumInteger($column, true);
    }
    public function bigIncrements($column)
    {
        return $this->unsignedBigInteger($column, true);
    }
    public function char($column, $length = null)
    {
        $length = $length ?: Builder::$defaultStringLength;
        return $this->addColumn('char', $column, compact('length'));
    }
    public function string($column, $length = null)
    {
        $length = $length ?: Builder::$defaultStringLength;
        return $this->addColumn('string', $column, compact('length'));
    }
    public function text($column)
    {
        return $this->addColumn('text', $column);
    }
    public function mediumText($column)
    {
        return $this->addColumn('mediumText', $column);
    }
    public function longText($column)
    {
        return $this->addColumn('longText', $column);
    }
    public function integer($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
    }
    public function tinyInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('tinyInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    public function smallInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('smallInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    public function mediumInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('mediumInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    public function bigInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }
    public function unsignedInteger($column, $autoIncrement = false)
    {
        return $this->integer($column, $autoIncrement, true);
    }
    public function unsignedTinyInteger($column, $autoIncrement = false)
    {
        return $this->tinyInteger($column, $autoIncrement, true);
    }
    public function unsignedSmallInteger($column, $autoIncrement = false)
    {
        return $this->smallInteger($column, $autoIncrement, true);
    }
    public function unsignedMediumInteger($column, $autoIncrement = false)
    {
        return $this->mediumInteger($column, $autoIncrement, true);
    }
    public function unsignedBigInteger($column, $autoIncrement = false)
    {
        return $this->bigInteger($column, $autoIncrement, true);
    }
    public function float($column, $total = 8, $places = 2)
    {
        return $this->addColumn('float', $column, compact('total', 'places'));
    }
    public function double($column, $total = null, $places = null)
    {
        return $this->addColumn('double', $column, compact('total', 'places'));
    }
    public function decimal($column, $total = 8, $places = 2)
    {
        return $this->addColumn('decimal', $column, compact('total', 'places'));
    }
    public function unsignedDecimal($column, $total = 8, $places = 2)
    {
        return $this->addColumn('decimal', $column, [
            'total' => $total, 'places' => $places, 'unsigned' => true,
        ]);
    }
    public function boolean($column)
    {
        return $this->addColumn('boolean', $column);
    }
    public function enum($column, array $allowed)
    {
        return $this->addColumn('enum', $column, compact('allowed'));
    }
    public function json($column)
    {
        return $this->addColumn('json', $column);
    }
    public function jsonb($column)
    {
        return $this->addColumn('jsonb', $column);
    }
    public function date($column)
    {
        return $this->addColumn('date', $column);
    }
    public function dateTime($column, $precision = 0)
    {
        return $this->addColumn('dateTime', $column, compact('precision'));
    }
    public function dateTimeTz($column, $precision = 0)
    {
        return $this->addColumn('dateTimeTz', $column, compact('precision'));
    }
    public function time($column, $precision = 0)
    {
        return $this->addColumn('time', $column, compact('precision'));
    }
    public function timeTz($column, $precision = 0)
    {
        return $this->addColumn('timeTz', $column, compact('precision'));
    }
    public function timestamp($column, $precision = 0)
    {
        return $this->addColumn('timestamp', $column, compact('precision'));
    }
    public function timestampTz($column, $precision = 0)
    {
        return $this->addColumn('timestampTz', $column, compact('precision'));
    }
    public function timestamps($precision = 0)
    {
        $this->timestamp('created_at', $precision)->nullable();
        $this->timestamp('updated_at', $precision)->nullable();
    }
    public function nullableTimestamps($precision = 0)
    {
        $this->timestamps($precision);
    }
    public function timestampsTz($precision = 0)
    {
        $this->timestampTz('created_at', $precision)->nullable();
        $this->timestampTz('updated_at', $precision)->nullable();
    }
    public function softDeletes($column = 'deleted_at', $precision = 0)
    {
        return $this->timestamp($column, $precision)->nullable();
    }
    public function softDeletesTz($column = 'deleted_at', $precision = 0)
    {
        return $this->timestampTz($column, $precision)->nullable();
    }
    public function year($column)
    {
        return $this->addColumn('year', $column);
    }
    public function binary($column)
    {
        return $this->addColumn('binary', $column);
    }
    public function uuid($column)
    {
        return $this->addColumn('uuid', $column);
    }
    public function ipAddress($column)
    {
        return $this->addColumn('ipAddress', $column);
    }
    public function macAddress($column)
    {
        return $this->addColumn('macAddress', $column);
    }
    public function geometry($column)
    {
        return $this->addColumn('geometry', $column);
    }
    public function point($column, $srid = null)
    {
        return $this->addColumn('point', $column, compact('srid'));
    }
    public function lineString($column)
    {
        return $this->addColumn('linestring', $column);
    }
    public function polygon($column)
    {
        return $this->addColumn('polygon', $column);
    }
    public function geometryCollection($column)
    {
        return $this->addColumn('geometrycollection', $column);
    }
    public function multiPoint($column)
    {
        return $this->addColumn('multipoint', $column);
    }
    public function multiLineString($column)
    {
        return $this->addColumn('multilinestring', $column);
    }
    public function multiPolygon($column)
    {
        return $this->addColumn('multipolygon', $column);
    }
    public function computed($column, $expression)
    {
        return $this->addColumn('computed', $column, compact('expression'));
    }
    public function morphs($name, $indexName = null)
    {
        $this->string("{$name}_type");
        $this->unsignedBigInteger("{$name}_id");
        $this->index(["{$name}_type", "{$name}_id"], $indexName);
    }
    public function nullableMorphs($name, $indexName = null)
    {
        $this->string("{$name}_type")->nullable();
        $this->unsignedBigInteger("{$name}_id")->nullable();
        $this->index(["{$name}_type", "{$name}_id"], $indexName);
    }
    public function rememberToken()
    {
        return $this->string('remember_token', 100)->nullable();
    }
    protected function indexCommand($type, $columns, $index, $algorithm = null)
    {
        $columns = (array) $columns;
        $index = $index ?: $this->createIndexName($type, $columns);
        return $this->addCommand(
            $type, compact('index', 'columns', 'algorithm')
        );
    }
    protected function dropIndexCommand($command, $type, $index)
    {
        $columns = [];
        if (is_array($index)) {
            $index = $this->createIndexName($type, $columns = $index);
        }
        return $this->indexCommand($command, $columns, $index);
    }
    protected function createIndexName($type, array $columns)
    {
        $index = strtolower($this->prefix.$this->table.'_'.implode('_', $columns).'_'.$type);
        return str_replace(['-', '.'], '_', $index);
    }
    public function addColumn($type, $name, array $parameters = [])
    {
        $this->columns[] = $column = new ColumnDefinition(
            array_merge(compact('type', 'name'), $parameters)
        );
        return $column;
    }
    public function removeColumn($name)
    {
        $this->columns = array_values(array_filter($this->columns, function ($c) use ($name) {
            return $c['name'] != $name;
        }));
        return $this;
    }
    protected function addCommand($name, array $parameters = [])
    {
        $this->commands[] = $command = $this->createCommand($name, $parameters);
        return $command;
    }
    protected function createCommand($name, array $parameters = [])
    {
        return new Fluent(array_merge(compact('name'), $parameters));
    }
    public function getTable()
    {
        return $this->table;
    }
    public function getColumns()
    {
        return $this->columns;
    }
    public function getCommands()
    {
        return $this->commands;
    }
    public function getAddedColumns()
    {
        return array_filter($this->columns, function ($column) {
            return ! $column->change;
        });
    }
    public function getChangedColumns()
    {
        return array_filter($this->columns, function ($column) {
            return (bool) $column->change;
        });
    }
}
