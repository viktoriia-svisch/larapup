<?php
namespace Illuminate\Database\Schema\Grammars;
use RuntimeException;
use Doctrine\DBAL\Types\Type;
use Illuminate\Support\Fluent;
use Doctrine\DBAL\Schema\Table;
use Illuminate\Database\Connection;
use Doctrine\DBAL\Schema\Comparator;
use Illuminate\Database\Schema\Blueprint;
use Doctrine\DBAL\Schema\AbstractSchemaManager as SchemaManager;
class ChangeColumn
{
    public static function compile($grammar, Blueprint $blueprint, Fluent $command, Connection $connection)
    {
        if (! $connection->isDoctrineAvailable()) {
            throw new RuntimeException(sprintf(
                'Changing columns for table "%s" requires Doctrine DBAL; install "doctrine/dbal".',
                $blueprint->getTable()
            ));
        }
        $tableDiff = static::getChangedDiff(
            $grammar, $blueprint, $schema = $connection->getDoctrineSchemaManager()
        );
        if ($tableDiff !== false) {
            return (array) $schema->getDatabasePlatform()->getAlterTableSQL($tableDiff);
        }
        return [];
    }
    protected static function getChangedDiff($grammar, Blueprint $blueprint, SchemaManager $schema)
    {
        $current = $schema->listTableDetails($grammar->getTablePrefix().$blueprint->getTable());
        return (new Comparator)->diffTable(
            $current, static::getTableWithColumnChanges($blueprint, $current)
        );
    }
    protected static function getTableWithColumnChanges(Blueprint $blueprint, Table $table)
    {
        $table = clone $table;
        foreach ($blueprint->getChangedColumns() as $fluent) {
            $column = static::getDoctrineColumn($table, $fluent);
            foreach ($fluent->getAttributes() as $key => $value) {
                if (! is_null($option = static::mapFluentOptionToDoctrine($key))) {
                    if (method_exists($column, $method = 'set'.ucfirst($option))) {
                        $column->{$method}(static::mapFluentValueToDoctrine($option, $value));
                    }
                }
            }
        }
        return $table;
    }
    protected static function getDoctrineColumn(Table $table, Fluent $fluent)
    {
        return $table->changeColumn(
            $fluent['name'], static::getDoctrineColumnChangeOptions($fluent)
        )->getColumn($fluent['name']);
    }
    protected static function getDoctrineColumnChangeOptions(Fluent $fluent)
    {
        $options = ['type' => static::getDoctrineColumnType($fluent['type'])];
        if (in_array($fluent['type'], ['text', 'mediumText', 'longText'])) {
            $options['length'] = static::calculateDoctrineTextLength($fluent['type']);
        }
        if ($fluent['type'] === 'json') {
            $options['customSchemaOptions'] = [
                'collation' => '',
            ];
        }
        return $options;
    }
    protected static function getDoctrineColumnType($type)
    {
        $type = strtolower($type);
        switch ($type) {
            case 'biginteger':
                $type = 'bigint';
                break;
            case 'smallinteger':
                $type = 'smallint';
                break;
            case 'mediumtext':
            case 'longtext':
                $type = 'text';
                break;
            case 'binary':
                $type = 'blob';
                break;
        }
        return Type::getType($type);
    }
    protected static function calculateDoctrineTextLength($type)
    {
        switch ($type) {
            case 'mediumText':
                return 65535 + 1;
            case 'longText':
                return 16777215 + 1;
            default:
                return 255 + 1;
        }
    }
    protected static function mapFluentOptionToDoctrine($attribute)
    {
        switch ($attribute) {
            case 'type':
            case 'name':
                return;
            case 'nullable':
                return 'notnull';
            case 'total':
                return 'precision';
            case 'places':
                return 'scale';
            default:
                return $attribute;
        }
    }
    protected static function mapFluentValueToDoctrine($option, $value)
    {
        return $option === 'notnull' ? ! $value : $value;
    }
}
