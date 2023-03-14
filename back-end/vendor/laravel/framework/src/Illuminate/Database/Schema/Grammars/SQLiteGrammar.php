<?php
namespace Illuminate\Database\Schema\Grammars;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Doctrine\DBAL\Schema\Index;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
class SQLiteGrammar extends Grammar
{
    protected $modifiers = ['Nullable', 'Default', 'Increment'];
    protected $serials = ['bigInteger', 'integer', 'mediumInteger', 'smallInteger', 'tinyInteger'];
    public function compileTableExists()
    {
        return "select * from sqlite_master where type = 'table' and name = ?";
    }
    public function compileColumnListing($table)
    {
        return 'pragma table_info('.$this->wrap(str_replace('.', '__', $table)).')';
    }
    public function compileCreate(Blueprint $blueprint, Fluent $command)
    {
        return sprintf('%s table %s (%s%s%s)',
            $blueprint->temporary ? 'create temporary' : 'create',
            $this->wrapTable($blueprint),
            implode(', ', $this->getColumns($blueprint)),
            (string) $this->addForeignKeys($blueprint),
            (string) $this->addPrimaryKeys($blueprint)
        );
    }
    protected function addForeignKeys(Blueprint $blueprint)
    {
        $foreigns = $this->getCommandsByName($blueprint, 'foreign');
        return collect($foreigns)->reduce(function ($sql, $foreign) {
            $sql .= $this->getForeignKey($foreign);
            if (! is_null($foreign->onDelete)) {
                $sql .= " on delete {$foreign->onDelete}";
            }
            if (! is_null($foreign->onUpdate)) {
                $sql .= " on update {$foreign->onUpdate}";
            }
            return $sql;
        }, '');
    }
    protected function getForeignKey($foreign)
    {
        return sprintf(', foreign key(%s) references %s(%s)',
            $this->columnize($foreign->columns),
            $this->wrapTable($foreign->on),
            $this->columnize((array) $foreign->references)
        );
    }
    protected function addPrimaryKeys(Blueprint $blueprint)
    {
        if (! is_null($primary = $this->getCommandByName($blueprint, 'primary'))) {
            return ", primary key ({$this->columnize($primary->columns)})";
        }
    }
    public function compileAdd(Blueprint $blueprint, Fluent $command)
    {
        $columns = $this->prefixArray('add column', $this->getColumns($blueprint));
        return collect($columns)->map(function ($column) use ($blueprint) {
            return 'alter table '.$this->wrapTable($blueprint).' '.$column;
        })->all();
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
        throw new RuntimeException('The database driver in use does not support spatial indexes.');
    }
    public function compileForeign(Blueprint $blueprint, Fluent $command)
    {
    }
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table '.$this->wrapTable($blueprint);
    }
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return 'drop table if exists '.$this->wrapTable($blueprint);
    }
    public function compileDropAllTables()
    {
        return "delete from sqlite_master where type in ('table', 'index', 'trigger')";
    }
    public function compileDropAllViews()
    {
        return "delete from sqlite_master where type in ('view')";
    }
    public function compileRebuild()
    {
        return 'vacuum';
    }
    public function compileDropColumn(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $tableDiff = $this->getDoctrineTableDiff(
            $blueprint, $schema = $connection->getDoctrineSchemaManager()
        );
        foreach ($command->columns as $name) {
            $tableDiff->removedColumns[$name] = $connection->getDoctrineColumn(
                $this->getTablePrefix().$blueprint->getTable(), $name
            );
        }
        return (array) $schema->getDatabasePlatform()->getAlterTableSQL($tableDiff);
    }
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "drop index {$index}";
    }
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        $index = $this->wrap($command->index);
        return "drop index {$index}";
    }
    public function compileDropSpatialIndex(Blueprint $blueprint, Fluent $command)
    {
        throw new RuntimeException('The database driver in use does not support spatial indexes.');
    }
    public function compileRename(Blueprint $blueprint, Fluent $command)
    {
        $from = $this->wrapTable($blueprint);
        return "alter table {$from} rename to ".$this->wrapTable($command->to);
    }
    public function compileRenameIndex(Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        $schemaManager = $connection->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($this->getTablePrefix().$blueprint->getTable());
        $index = Arr::get($indexes, $command->from);
        if (! $index) {
            throw new RuntimeException("Index [{$command->from}] does not exist.");
        }
        $newIndex = new Index(
            $command->to, $index->getColumns(), $index->isUnique(),
            $index->isPrimary(), $index->getFlags(), $index->getOptions()
        );
        $platform = $schemaManager->getDatabasePlatform();
        return [
            $platform->getDropIndexSQL($command->from, $this->getTablePrefix().$blueprint->getTable()),
            $platform->getCreateIndexSQL($newIndex, $this->getTablePrefix().$blueprint->getTable()),
        ];
    }
    public function compileEnableForeignKeyConstraints()
    {
        return 'PRAGMA foreign_keys = ON;';
    }
    public function compileDisableForeignKeyConstraints()
    {
        return 'PRAGMA foreign_keys = OFF;';
    }
    public function compileEnableWriteableSchema()
    {
        return 'PRAGMA writable_schema = 1;';
    }
    public function compileDisableWriteableSchema()
    {
        return 'PRAGMA writable_schema = 0;';
    }
    protected function typeChar(Fluent $column)
    {
        return 'varchar';
    }
    protected function typeString(Fluent $column)
    {
        return 'varchar';
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
        return 'integer';
    }
    protected function typeBigInteger(Fluent $column)
    {
        return 'integer';
    }
    protected function typeMediumInteger(Fluent $column)
    {
        return 'integer';
    }
    protected function typeTinyInteger(Fluent $column)
    {
        return 'integer';
    }
    protected function typeSmallInteger(Fluent $column)
    {
        return 'integer';
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
        return 'numeric';
    }
    protected function typeBoolean(Fluent $column)
    {
        return 'tinyint(1)';
    }
    protected function typeEnum(Fluent $column)
    {
        return sprintf(
            'varchar check ("%s" in (%s))',
            $column->name,
            $this->quoteString($column->allowed)
        );
    }
    protected function typeJson(Fluent $column)
    {
        return 'text';
    }
    protected function typeJsonb(Fluent $column)
    {
        return 'text';
    }
    protected function typeDate(Fluent $column)
    {
        return 'date';
    }
    protected function typeDateTime(Fluent $column)
    {
        return 'datetime';
    }
    protected function typeDateTimeTz(Fluent $column)
    {
        return $this->typeDateTime($column);
    }
    protected function typeTime(Fluent $column)
    {
        return 'time';
    }
    protected function typeTimeTz(Fluent $column)
    {
        return $this->typeTime($column);
    }
    protected function typeTimestamp(Fluent $column)
    {
        return $column->useCurrent ? 'datetime default CURRENT_TIMESTAMP' : 'datetime';
    }
    protected function typeTimestampTz(Fluent $column)
    {
        return $this->typeTimestamp($column);
    }
    protected function typeYear(Fluent $column)
    {
        return $this->typeInteger($column);
    }
    protected function typeBinary(Fluent $column)
    {
        return 'blob';
    }
    protected function typeUuid(Fluent $column)
    {
        return 'varchar';
    }
    protected function typeIpAddress(Fluent $column)
    {
        return 'varchar';
    }
    protected function typeMacAddress(Fluent $column)
    {
        return 'varchar';
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
        if (in_array($column->type, $this->serials) && $column->autoIncrement) {
            return ' primary key autoincrement';
        }
    }
}
