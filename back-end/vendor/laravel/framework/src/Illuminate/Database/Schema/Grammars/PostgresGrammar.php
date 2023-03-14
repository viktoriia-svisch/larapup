<?php
namespace Illuminate\Database\Schema\Grammars;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
class PostgresGrammar extends Grammar
{
    protected $transactions = true;
    protected $modifiers = ['Increment', 'Nullable', 'Default'];
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];
    protected $fluentCommands = ['Comment'];
    public function compileTableExists()
    {
        return 'select * from information_schema.tables where table_schema = ? and table_name = ?';
    }
    public function compileColumnListing()
    {
        return 'select column_name from information_schema.columns where table_schema = ? and table_name = ?';
    }
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('%s table %s (%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        );
    }
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter table %s %s',
            $this->wrapTable($blueprint),
            implode(', ', $this->prefixArray('add column', $this->getColumns($blueprint)))
        );
    }
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->columnize($command->columns);
        return 'alter table '.$this->wrapTable($blueprint)." add primary key ({$columns})";
    }
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter table %s add constraint %s unique (%s)',
            $this->wrapTable($blueprint),
            $this->wrap($command->index),
            $this->columnize($command->columns)
        );
    }
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('create index %s on %s%s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $command->algorithm ? ' using '.$command->algorithm : '',
            $this->columnize($command->columns)
        );
    }
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        $command->algorithm = 'gist';
        return $this->compileIndex($blueprint, $command);
    }
    public function compileForeign(Blueprint $blueprint, Fluent $command)
    {
        $sql = parent::compileForeign($blueprint, $command);
        if (! is_null($command->deferrable)) {
            $sql .= $command->deferrable ? ' deferrable' : ' not deferrable';
        }
        if ($command->deferrable && ! is_null($command->initiallyImmediate)) {
            $sql .= $command->initiallyImmediate ? ' initially immediate' : ' initially deferred';
        }
        if (! is_null($command->notValid)) {
            $sql .= ' not valid';
        }
        return $sql;
    }
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table if exists '.$this->wrapTable($blueprint);
    }
    public function compileDropAllTables($tables)
    {
        return 'drop table "'.implode('","', $tables).'" cascade';
    }
    public function compileDropAllViews($views)
    {
        return 'drop view "'.implode('","', $views).'" cascade';
    }
    public function compileGetAllTables($schema)
    {
        return "select tablename from pg_catalog.pg_tables where schemaname = '{$schema}'";
    }
    public function compileGetAllViews($schema)
    {
        return "select viewname from pg_catalog.pg_views where schemaname = '{$schema}'";
    }
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('drop column', $this->wrapArray($command->columns));
        return 'alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
    }
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap("{$blueprint->getTable()}_pkey");
        return 'alter table '.$this->wrapTable($blueprint)." drop constraint {$index}";
    }
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
    }
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        return "drop index {$this->wrap($command->index)}";
    }
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDropIndex($blueprint, $command);
    }
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
    }
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);
        return "alter table {$from} rename to ".$this->wrapTable($command->to);
    }
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter index %s rename to %s',
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }
    public function compileEnableForeignKeyConstraints()
    {
        return 'SET CONSTRAINTS ALL IMMEDIATE;';
    }
    public function compileDisableForeignKeyConstraints()
    {
        return 'SET CONSTRAINTS ALL DEFERRED;';
    }
    public function compileComment(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('comment on column %s.%s is %s',
            $this->wrapTable($blueprint),
            $this->wrap($command->column->name),
            "'".str_replace("'", "''", $command->value)."'"
        );
    }
    protected function typeChar(Fluent $column)
    {
        return "char({$column->length})";
    }
    protected function typeString(Fluent $column)
    {
        return "varchar({$column->length})";
    }
    protected function typeText(Fluent $column)
    {
        return 'text';
    }
    protected function typeMediumText(Fluent $column)
    {
        return 'text';
    }
    protected function typeLongText(Fluent $column)
    {
        return 'text';
    }
    protected function typeInteger(Fluent $column)
    {
        return $this->generatableColumn('integer', $column);
    }
    protected function typeBigInteger(Fluent $column)
    {
        return $this->generatableColumn('bigint', $column);
    }
    protected function typeMediumInteger(Fluent $column)
    {
        return $this->generatableColumn('integer', $column);
    }
    protected function typeTinyInteger(Fluent $column)
    {
        return $this->generatableColumn('smallint', $column);
    }
    protected function typeSmallInteger(Fluent $column)
    {
        return $this->generatableColumn('smallint', $column);
    }
    protected function generatableColumn($type, Fluent $column)
    {
        if (! $column->autoIncrement && is_null($column->generatedAs)) {
            return $type;
        }
        if ($column->autoIncrement && is_null($column->generatedAs)) {
            return with([
                'integer' => 'serial',
                'bigint' => 'bigserial',
                'smallint' => 'smallserial',
            ])[$type];
        }
        $options = '';
        if (! is_bool($column->generatedAs) && ! empty($column->generatedAs)) {
            $options = sprintf(' (%s)', $column->generatedAs);
        }
        return sprintf(
            '%s generated %s as identity%s',
            $type,
            $column->always ? 'always' : 'by default',
            $options
        );
    }
    protected function typeFloat(Fluent $column)
    {
        return $this->typeDouble($column);
    }
    protected function typeDouble(Fluent $column)
    {
        return 'double precision';
    }
    protected function typeReal(Fluent $column)
    {
        return 'real';
    }
    protected function typeDecimal(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }
    protected function typeBoolean(Fluent $column)
    {
        return 'boolean';
    }
    protected function typeEnum(Fluent $column)
    {
        return sprintf(
            'varchar(255) check ("%s" in (%s))',
            $column->name,
            $this->quoteString($column->allowed)
        );
    }
    protected function typeJson(Fluent $column)
    {
        return 'json';
    }
    protected function typeJsonb(Fluent $column)
    {
        return 'jsonb';
    }
    protected function typeDate(Fluent $column)
    {
        return 'date';
    }
    protected function typeDateTime(Fluent $column)
    {
        return "timestamp($column->precision) without time zone";
    }
    protected function typeDateTimeTz(Fluent $column)
    {
        return "timestamp($column->precision) with time zone";
    }
    protected function typeTime(Fluent $column)
    {
        return "time($column->precision) without time zone";
    }
    protected function typeTimeTz(Fluent $column)
    {
        return "time($column->precision) with time zone";
    }
    protected function typeTimestamp(Fluent $column)
    {
        $columnType = "timestamp($column->precision) without time zone";
        return $column->useCurrent ? "$columnType default CURRENT_TIMESTAMP" : $columnType;
    }
    protected function typeTimestampTz(Fluent $column)
    {
        $columnType = "timestamp($column->precision) with time zone";
        return $column->useCurrent ? "$columnType default CURRENT_TIMESTAMP" : $columnType;
    }
    protected function typeYear(Fluent $column)
    {
        return $this->typeInteger($column);
    }
    protected function typeBinary(Fluent $column)
    {
        return 'bytea';
    }
    protected function typeUuid(Fluent $column)
    {
        return 'uuid';
    }
    protected function typeIpAddress(Fluent $column)
    {
        return 'inet';
    }
    protected function typeMacAddress(Fluent $column)
    {
        return 'macaddr';
    }
    protected function typeGeometry(Fluent $column)
    {
        return $this->formatPostGisType('geometry');
    }
    protected function typePoint(Fluent $column)
    {
        return $this->formatPostGisType('point');
    }
    protected function typeLineString(Fluent $column)
    {
        return $this->formatPostGisType('linestring');
    }
    protected function typePolygon(Fluent $column)
    {
        return $this->formatPostGisType('polygon');
    }
    protected function typeGeometryCollection(Fluent $column)
    {
        return $this->formatPostGisType('geometrycollection');
    }
    protected function typeMultiPoint(Fluent $column)
    {
        return $this->formatPostGisType('multipoint');
    }
    public function typeMultiLineString(Fluent $column)
    {
        return $this->formatPostGisType('multilinestring');
    }
    protected function typeMultiPolygon(Fluent $column)
    {
        return $this->formatPostGisType('multipolygon');
    }
    private function formatPostGisType(string $type)
    {
        return "geography($type, 4326)";
    }
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        return $column->nullable ? ' null' : ' not null';
    }
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->default)) {
            return ' default '.$this->getDefaultValue($column->default);
        }
    }
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
    {
        if ((in_array($column->type, $this->serials) || ($column->generatedAs !== null)) && $column->autoIncrement) {
            return ' primary key';
        }
    }
}
