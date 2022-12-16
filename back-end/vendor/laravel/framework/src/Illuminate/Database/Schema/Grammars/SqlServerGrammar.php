<?php
namespace Illuminate\Database\Schema\Grammars;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;
class SqlServerGrammar extends Grammar
{
    protected $transactions = true;
    protected $modifiers = ['Increment', 'Collate', 'Nullable', 'Default', 'Persisted'];
    protected $serials = ['tinyInteger', 'smallInteger', 'mediumInteger', 'integer', 'bigInteger'];
    public function compileTableExists()
    {
        return "select * from sysobjects where type = 'U' and name = ?";
    }
    public function compileColumnListing($table)
    {
        return "select col.name from sys.columns as col
                join sys.objects as obj on col.object_id = obj.object_id
                where obj.type = 'U' and obj.name = '$table'";
    }
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        $columns = implode(', ', $this->getColumns($blueprint));
        return 'create table '.$this->wrapTable($blueprint)." ($columns)";
    }
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter table %s add %s',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        );
    }
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter table %s add constraint %s primary key (%s)',
            $this->wrapTable($blueprint),
            $this->wrap($command->index),
            $this->columnize($command->columns)
        );
    }
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('create unique index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('create index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('create spatial index %s on %s (%s)',
            $this->wrap($command->index),
            $this->wrapTable($blueprint),
            $this->columnize($command->columns)
        );
    }
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('if exists (select * from INFORMATION_SCHEMA.TABLES where TABLE_NAME = %s) drop table %s',
            "'".str_replace("'", "''", $this->getTablePrefix().$blueprint->getTable())."'",
            $this->wrapTable($blueprint)
        );
    }
    public function compileDropAllTables()
    {
        return "EXEC sp_msforeachtable 'DROP TABLE ?'";
    }
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->wrapArray($command->columns);
        return 'alter table '.$this->wrapTable($blueprint).' drop column '.implode(', ', $columns);
    }
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "alter table {$this->wrapTable($blueprint)} drop constraint {$index}";
    }
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "drop index {$index} on {$this->wrapTable($blueprint)}";
    }
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "drop index {$index} on {$this->wrapTable($blueprint)}";
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
        return "sp_rename {$from}, ".$this->wrapTable($command->to);
    }
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf("sp_rename N'%s', %s, N'INDEX'",
            $this->wrap($blueprint->getTable().'.'.$command->from),
            $this->wrap($command->to)
        );
    }
    public function compileEnableForeignKeyConstraints()
    {
        return 'EXEC sp_msforeachtable @command1="print \'?\'", @command2="ALTER TABLE ? WITH CHECK CHECK CONSTRAINT all";';
    }
    public function compileDisableForeignKeyConstraints()
    {
        return 'EXEC sp_msforeachtable "ALTER TABLE ? NOCHECK CONSTRAINT all";';
    }
    protected function typeChar(Fluent $column)
    {
        return "nchar({$column->length})";
    }
    protected function typeString(Fluent $column)
    {
        return "nvarchar({$column->length})";
    }
    protected function typeText(Fluent $column)
    {
        return 'nvarchar(max)';
    }
    protected function typeMediumText(Fluent $column)
    {
        return 'nvarchar(max)';
    }
    protected function typeLongText(Fluent $column)
    {
        return 'nvarchar(max)';
    }
    protected function typeInteger(Fluent $column)
    {
        return 'int';
    }
    protected function typeBigInteger(Fluent $column)
    {
        return 'bigint';
    }
    protected function typeMediumInteger(Fluent $column)
    {
        return 'int';
    }
    protected function typeTinyInteger(Fluent $column)
    {
        return 'tinyint';
    }
    protected function typeSmallInteger(Fluent $column)
    {
        return 'smallint';
    }
    protected function typeFloat(Fluent $column)
    {
        return 'float';
    }
    protected function typeDouble(Fluent $column)
    {
        return 'float';
    }
    protected function typeDecimal(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }
    protected function typeBoolean(Fluent $column)
    {
        return 'bit';
    }
    protected function typeEnum(Fluent $column)
    {
        return sprintf(
            'nvarchar(255) check ("%s" in (%s))',
            $column->name,
            $this->quoteString($column->allowed)
        );
    }
    protected function typeJson(Fluent $column)
    {
        return 'nvarchar(max)';
    }
    protected function typeJsonb(Fluent $column)
    {
        return 'nvarchar(max)';
    }
    protected function typeDate(Fluent $column)
    {
        return 'date';
    }
    protected function typeDateTime(Fluent $column)
    {
        return $column->precision ? "datetime2($column->precision)" : 'datetime';
    }
    protected function typeDateTimeTz(Fluent $column)
    {
        return $column->precision ? "datetimeoffset($column->precision)" : 'datetimeoffset';
    }
    protected function typeTime(Fluent $column)
    {
        return $column->precision ? "time($column->precision)" : 'time';
    }
    protected function typeTimeTz(Fluent $column)
    {
        return $this->typeTime($column);
    }
    protected function typeTimestamp(Fluent $column)
    {
        $columnType = $column->precision ? "datetime2($column->precision)" : 'datetime';
        return $column->useCurrent ? "$columnType default CURRENT_TIMESTAMP" : $columnType;
    }
    protected function typeTimestampTz(Fluent $column)
    {
        if ($column->useCurrent) {
            $columnType = $column->precision ? "datetimeoffset($column->precision)" : 'datetimeoffset';
            return "$columnType default CURRENT_TIMESTAMP";
        }
        return "datetimeoffset($column->precision)";
    }
    protected function typeYear(Fluent $column)
    {
        return $this->typeInteger($column);
    }
    protected function typeBinary(Fluent $column)
    {
        return 'varbinary(max)';
    }
    protected function typeUuid(Fluent $column)
    {
        return 'uniqueidentifier';
    }
    protected function typeIpAddress(Fluent $column)
    {
        return 'nvarchar(45)';
    }
    protected function typeMacAddress(Fluent $column)
    {
        return 'nvarchar(17)';
    }
    public function typeGeometry(Fluent $column)
    {
        return 'geography';
    }
    public function typePoint(Fluent $column)
    {
        return 'geography';
    }
    public function typeLineString(Fluent $column)
    {
        return 'geography';
    }
    public function typePolygon(Fluent $column)
    {
        return 'geography';
    }
    public function typeGeometryCollection(Fluent $column)
    {
        return 'geography';
    }
    public function typeMultiPoint(Fluent $column)
    {
        return 'geography';
    }
    public function typeMultiLineString(Fluent $column)
    {
        return 'geography';
    }
    public function typeMultiPolygon(Fluent $column)
    {
        return 'geography';
    }
    protected function typeComputed(Fluent $column)
    {
        return "as ({$column->expression})";
    }
    protected function modifyCollate(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->collation)) {
            return ' collate '.$column->collation;
        }
    }
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        if ($column->type !== 'computed') {
            return $column->nullable ? ' null' : ' not null';
        }
    }
    protected function modifyDefault(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->default)) {
            return ' default '.$this->getDefaultValue($column->default);
        }
    }
    protected function modifyIncrement(Blueprint $blueprint, Fluent $column)
    {
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' identity primary key';
        }
    }
    protected function modifyPersisted(Blueprint $blueprint, Fluent $column)
    {
        if ($column->persisted) {
            return ' persisted';
        }
    }
    public function wrapTable($table)
    {
        if ($table instanceof Blueprint && $table->temporary) {
            $this->setTablePrefix('#');
        }
        return parent::wrapTable($table);
    }
}
