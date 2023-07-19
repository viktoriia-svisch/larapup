<?php
namespace Faker\ORM\Propel2;
use \Faker\Provider\Base;
use \Propel\Runtime\Map\ColumnMap;
class EntityPopulator
{
    protected $class;
    protected $columnFormatters = array();
    protected $modifiers = array();
    public function __construct($class)
    {
        $this->class = $class;
    }
    public function getClass()
    {
        return $this->class;
    }
    public function setColumnFormatters($columnFormatters)
    {
        $this->columnFormatters = $columnFormatters;
    }
    public function getColumnFormatters()
    {
        return $this->columnFormatters;
    }
    public function mergeColumnFormattersWith($columnFormatters)
    {
        $this->columnFormatters = array_merge($this->columnFormatters, $columnFormatters);
    }
    public function guessColumnFormatters(\Faker\Generator $generator)
    {
        $formatters = array();
        $class = $this->class;
        $peerClass = $class::TABLE_MAP;
        $tableMap = $peerClass::getTableMap();
        $nameGuesser = new \Faker\Guesser\Name($generator);
        $columnTypeGuesser = new \Faker\ORM\Propel2\ColumnTypeGuesser($generator);
        foreach ($tableMap->getColumns() as $columnMap) {
            if ($this->isColumnBehavior($columnMap)) {
                continue;
            }
            if ($columnMap->isForeignKey()) {
                $relatedClass = $columnMap->getRelation()->getForeignTable()->getClassname();
                $formatters[$columnMap->getPhpName()] = function ($inserted) use ($relatedClass) {
                    $relatedClass = trim($relatedClass, "\\");
                    return isset($inserted[$relatedClass]) ? $inserted[$relatedClass][mt_rand(0, count($inserted[$relatedClass]) - 1)] : null;
                };
                continue;
            }
            if ($columnMap->isPrimaryKey()) {
                continue;
            }
            if ($formatter = $nameGuesser->guessFormat($columnMap->getPhpName(), $columnMap->getSize())) {
                $formatters[$columnMap->getPhpName()] = $formatter;
                continue;
            }
            if ($formatter = $columnTypeGuesser->guessFormat($columnMap)) {
                $formatters[$columnMap->getPhpName()] = $formatter;
                continue;
            }
        }
        return $formatters;
    }
    protected function isColumnBehavior(ColumnMap $columnMap)
    {
        foreach ($columnMap->getTable()->getBehaviors() as $name => $params) {
            $columnName = Base::toLower($columnMap->getName());
            switch ($name) {
                case 'nested_set':
                    $columnNames = array($params['left_column'], $params['right_column'], $params['level_column']);
                    if (in_array($columnName, $columnNames)) {
                        return true;
                    }
                    break;
                case 'timestampable':
                    $columnNames = array($params['create_column'], $params['update_column']);
                    if (in_array($columnName, $columnNames)) {
                        return true;
                    }
                    break;
            }
        }
        return false;
    }
    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }
    public function getModifiers()
    {
        return $this->modifiers;
    }
    public function mergeModifiersWith($modifiers)
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);
    }
    public function guessModifiers(\Faker\Generator $generator)
    {
        $modifiers = array();
        $class = $this->class;
        $peerClass = $class::TABLE_MAP;
        $tableMap = $peerClass::getTableMap();
        foreach ($tableMap->getBehaviors() as $name => $params) {
            switch ($name) {
                case 'nested_set':
                    $modifiers['nested_set'] = function ($obj, $inserted) use ($class, $generator) {
                        if (isset($inserted[$class])) {
                            $queryClass = $class . 'Query';
                            $parent = $queryClass::create()->findPk($generator->randomElement($inserted[$class]));
                            $obj->insertAsLastChildOf($parent);
                        } else {
                            $obj->makeRoot();
                        }
                    };
                    break;
                case 'sortable':
                    $modifiers['sortable'] = function ($obj, $inserted) use ($class) {
                        $maxRank = isset($inserted[$class]) ? count($inserted[$class]) : 0;
                        $obj->insertAtRank(mt_rand(1, $maxRank + 1));
                    };
                    break;
            }
        }
        return $modifiers;
    }
    public function execute($con, $insertedEntities)
    {
        $obj = new $this->class();
        foreach ($this->getColumnFormatters() as $column => $format) {
            if (null !== $format) {
                $obj->setByName($column, is_callable($format) ? $format($insertedEntities, $obj) : $format);
            }
        }
        foreach ($this->getModifiers() as $modifier) {
            $modifier($obj, $insertedEntities);
        }
        $obj->save($con);
        return $obj->getPrimaryKey();
    }
}