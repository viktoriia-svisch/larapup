<?php
namespace Illuminate\Database\Schema\Grammars;
use RuntimeException;
use Illuminate\Support\Fluent;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
class MySqlGrammar extends Grammar
{
    protected $modifiers = [
        'Unsigned', 'VirtualAs', 'StoredAs', 'Charset', 'Collate', 'Nullable',
        'Default', 'Increment', 'Comment', 'After', 'First', 'Srid',
    ];
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];
    public function compileTableExists()
    {
        return 'select * from information_schema.tables where table_schema = ? and table_name = ?';
    }
    public function compileColumnListing()
    {
        return 'select column_name as `column_name` from information_schema.columns where table_schema = ? and table_name = ?';
    }
    public function compileCreate(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $sql = $this->compileCreateTable(
            $blueprint, $command, $connection
        );
        $sql = $this->compileCreateEncoding(
            $sql, $connection, $blueprint
        );
        return $this->compileCreateEngine(
            $sql, $connection, $blueprint
        );
    }
    protected function compileCreateTable($blueprint, $command, $connection)
    {
        return sprintf('%s table %s (%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint))
        );
    }
    protected function compileCreateEncoding($sql, Connection $connection, Blueprint $blueprint)
    {
        if (isset($blueprint->charset)) {
            $sql .= ' default character set '.$blueprint->charset;
        } elseif (! is_null($charset = $connection->getConfig('charset'))) {
            $sql .= ' default character set '.$charset;
        }
        if (isset($blueprint->collation)) {
            $sql .= " collate '{$blueprint->collation}'";
        } elseif (! is_null($collation = $connection->getConfig('collation'))) {
            $sql .= " collate '{$collation}'";
        }
        return $sql;
    }
    protected function compileCreateEngine($sql, Connection $connection, Blueprint $blueprint)
    {
        if (isset($blueprint->engine)) {
            return $sql.' engine = '.$blueprint->engine;
        } elseif (! is_null($engine = $connection->getConfig('engine'))) {
            return $sql.' engine = '.$engine;
        }
        return $sql;
    }
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('add', $this->getColumns($blueprint));
        return 'alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
    }
    public function compilePrimary(Blueprint $blueprint, Fluent $command)
    {
        $command->name(null);
        return $this->compileKey($blueprint, $command, 'primary key');
    }
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'unique');
    }
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'index');
    }
    public function compileSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileKey($blueprint, $command, 'spatial index');
    }
    protected function compileKey(Blueprint $blueprint, Fluent $command, $type)
    {
        return sprintf('alter table %s add %s %s%s(%s)',
            $this->wrapTable($blueprint),
            $type,
            $this->wrap($command->index),
            $command->algorithm ? ' using '.$command->algorithm : '',
            $this->columnize($command->columns)
        );
    }
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table if exists '.$this->wrapTable($blueprint);
    }
    public function compileDropColumn(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('drop', $this->wrapArray($command->columns));
        return 'alter table '.$this->wrapTable($blueprint).' '.implode(', ', $columns);
    }
    public function compileDropPrimary(Blueprint $blueprint, Fluent $command)
    {
        return 'alter table '.$this->wrapTable($blueprint).' drop primary key';
    }
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
    }
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "alter table {$this->wrapTable($blueprint)} drop index {$index}";
    }
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDropIndex($blueprint, $command);
    }
    public function compileDropForeign(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "alter table {$this->wrapTable($blueprint)} drop foreign key {$index}";
    }
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);
        return "rename table {$from} to ".$this->wrapTable($command->to);
    }
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('alter table %s rename index %s to %s',
            $this->wrapTable($blueprint),
            $this->wrap($command->from),
            $this->wrap($command->to)
        );
    }
    public function compileDropAllTables($tables)
    {
        return 'drop table '.implode(',', $this->wrapArray($tables));
    }
    public function compileDropAllViews($views)
    {
        return 'drop view '.implode(',', $this->wrapArray($views));
    }
    public function compileGetAllTables()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'BASE TABLE\'';
    }
    public function compileGetAllViews()
    {
        return 'SHOW FULL TABLES WHERE table_type = \'VIEW\'';
    }
    public function compileEnableForeignKeyConstraints()
    {
        return 'SET FOREIGN_KEY_CHECKS=1;';
    }
    public function compileDisableForeignKeyConstraints()
    {
        return 'SET FOREIGN_KEY_CHECKS=0;';
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
        return 'mediumtext';
    }
    protected function typeLongText(Fluent $column)
    {
        return 'longtext';
    }
    protected function typeBigInteger(Fluent $column)
    {
        return 'bigint';
    }
    protected function typeInteger(Fluent $column)
    {
        return 'int';
    }
    protected function typeMediumInteger(Fluent $column)
    {
        return 'mediumint';
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
        return $this->typeDouble($column);
    }
    protected function typeDouble(Fluent $column)
    {
        if ($column->total && $column->places) {
            return "double({$column->total}, {$column->places})";
        }
        return 'double';
    }
    protected function typeDecimal(Fluent $column)
    {
        return "decimal({$column->total}, {$column->places})";
    }
    protected function typeBoolean(Fluent $column)
    {
        return 'tinyint(1)';
    }
    protected function typeEnum(Fluent $column)
    {
        return sprintf('enum(%s)', $this->quoteString($column->allowed));
    }
    protected function typeJson(Fluent $column)
    {
        return 'json';
    }
    protected function typeJsonb(Fluent $column)
    {
        return 'json';
    }
    protected function typeDate(Fluent $column)
    {
        return 'date';
    }
    protected function typeDateTime(Fluent $column)
    {
        return $column->precision ? "datetime($column->precision)" : 'datetime';
    }
    protected function typeDateTimeTz(Fluent $column)
    {
        return $this->typeDateTime($column);
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
        $columnType = $column->precision ? "timestamp($column->precision)" : 'timestamp';
        return $column->useCurrent ? "$columnType default CURRENT_TIMESTAMP" : $columnType;
    }
    protected function typeTimestampTz(Fluent $column)
    {
        return $this->typeTimestamp($column);
    }
    protected function typeYear(Fluent $column)
    {
        return 'year';
    }
    protected function typeBinary(Fluent $column)
    {
        return 'blob';
    }
    protected function typeUuid(Fluent $column)
    {
        return 'char(36)';
    }
    protected function typeIpAddress(Fluent $column)
    {
        return 'varchar(45)';
    }
    protected function typeMacAddress(Fluent $column)
    {
        return 'varchar(17)';
    }
    public function typeGeometry(Fluent $column)
    {
        return 'geometry';
    }
    public function typePoint(Fluent $column)
    {
        return 'point';
    }
    public function typeLineString(Fluent $column)
    {
        return 'linestring';
    }
    public function typePolygon(Fluent $column)
    {
        return 'polygon';
    }
    public function typeGeometryCollection(Fluent $column)
    {
        return 'geometrycollection';
    }
    public function typeMultiPoint(Fluent $column)
    {
        return 'multipoint';
    }
    public function typeMultiLineString(Fluent $column)
    {
        return 'multilinestring';
    }
    public function typeMultiPolygon(Fluent $column)
    {
        return 'multipolygon';
    }
    protected function typeComputed(Fluent $column)
    {
        throw new RuntimeException('This database driver requires a type, see the virtualAs / storedAs modifiers.');
    }
    protected function modifyVirtualAs(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->virtualAs)) {
            return " as ({$column->virtualAs})";
        }
    }
    protected function modifyStoredAs(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->storedAs)) {
            return " as ({$column->storedAs}) stored";
        }
    }
    protected function modifyUnsigned(Blueprint $blueprint, Fluent $column)
    {
        if ($column->unsigned) {
            return ' unsigned';
        }
    }
    protected function modifyCharset(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->charset)) {
            return ' character set '.$column->charset;
        }
    }
    protected function modifyCollate(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->collation)) {
            return " collate '{$column->collation}'";
        }
    }
    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        if (is_null($column->virtualAs) && is_null($column->storedAs)) {
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
            return ' auto_increment primary key';
        }
    }
    protected function modifyFirst(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->first)) {
            return ' first';
        }
    }
    protected function modifyAfter(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->after)) {
            return ' after '.$this->wrap($column->after);
        }
    }
    protected function modifyComment(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->comment)) {
            return " comment '".addslashes($column->comment)."'";
        }
    }
    protected function modifySrid(Blueprint $blueprint, Fluent $column)
    {
        if (! is_null($column->srid) && is_int($column->srid) && $column->srid > 0) {
            return ' srid '.$column->srid;
        }
    }
    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return '`'.str_replace('`', '``', $value).'`';
        }
        return $value;
    }
}
