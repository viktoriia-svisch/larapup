<?php
namespace Illuminate\Database\Query\Grammars;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;
class SQLiteGrammar extends Grammar
{
    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'lock',
    ];
    protected $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
        'like', 'not like', 'ilike',
        '&', '|', '<<', '>>',
    ];
    public function compileSelect(Builder $query)
    {
        if ($query->unions && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }
        $sql = parent::compileSelect($query);
        if ($query->unions) {
            $sql = 'select * from ('.$sql.') '.$this->compileUnions($query);
        }
        return $sql;
    }
    protected function compileUnion(array $union)
    {
        $conjunction = $union['all'] ? ' union all ' : ' union ';
        return $conjunction.'select * from ('.$union['query']->toSql().')';
    }
    protected function whereDate(Builder $query, $where)
    {
        return $this->dateBasedWhere('%Y-%m-%d', $query, $where);
    }
    protected function whereDay(Builder $query, $where)
    {
        return $this->dateBasedWhere('%d', $query, $where);
    }
    protected function whereMonth(Builder $query, $where)
    {
        return $this->dateBasedWhere('%m', $query, $where);
    }
    protected function whereYear(Builder $query, $where)
    {
        return $this->dateBasedWhere('%Y', $query, $where);
    }
    protected function whereTime(Builder $query, $where)
    {
        return $this->dateBasedWhere('%H:%M:%S', $query, $where);
    }
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        return "strftime('{$type}', {$this->wrap($where['column'])}) {$where['operator']} cast({$value} as text)";
    }
    protected function compileJsonLength($column, $operator, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);
        return 'json_array_length('.$field.$path.') '.$operator.' '.$value;
    }
    public function compileInsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);
        if (! is_array(reset($values))) {
            $values = [$values];
        }
        if (count($values) === 1) {
            return empty(reset($values))
                    ? "insert into $table default values"
                    : parent::compileInsert($query, reset($values));
        }
        $names = $this->columnize(array_keys(reset($values)));
        $columns = [];
        foreach (array_keys(reset($values)) as $column) {
            $columns[] = '? as '.$this->wrap($column);
        }
        $columns = array_fill(0, count($values), implode(', ', $columns));
        return "insert into $table ($names) select ".implode(' union all select ', $columns);
    }
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);
        $columns = collect($values)->map(function ($value, $key) use ($query) {
            return $this->wrap(Str::after($key, $query->from.'.')).' = '.$this->parameter($value);
        })->implode(', ');
        if (isset($query->joins) || isset($query->limit)) {
            $selectSql = parent::compileSelect($query->select("{$query->from}.rowid"));
            return "update {$table} set $columns where {$this->wrap('rowid')} in ({$selectSql})";
        }
        return trim("update {$table} set {$columns} {$this->compileWheres($query)}");
    }
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $cleanBindings = Arr::except($bindings, ['select', 'join']);
        return array_values(
            array_merge($values, $bindings['join'], Arr::flatten($cleanBindings))
        );
    }
    public function compileDelete(Builder $query)
    {
        if (isset($query->joins) || isset($query->limit)) {
            $selectSql = parent::compileSelect($query->select("{$query->from}.rowid"));
            return "delete from {$this->wrapTable($query->from)} where {$this->wrap('rowid')} in ({$selectSql})";
        }
        $wheres = is_array($query->wheres) ? $this->compileWheres($query) : '';
        return trim("delete from {$this->wrapTable($query->from)} $wheres");
    }
    public function prepareBindingsForDelete(array $bindings)
    {
        $cleanBindings = Arr::except($bindings, ['select', 'join']);
        return array_values(
            array_merge($bindings['join'], Arr::flatten($cleanBindings))
        );
    }
    public function compileTruncate(Builder $query)
    {
        return [
            'delete from sqlite_sequence where name = ?' => [$query->from],
            'delete from '.$this->wrapTable($query->from) => [],
        ];
    }
    protected function wrapJsonSelector($value)
    {
        $parts = explode('->', $value, 2);
        $field = $this->wrap($parts[0]);
        $path = count($parts) > 1 ? ', '.$this->wrapJsonPath($parts[1]) : '';
        return 'json_extract('.$field.$path.')';
    }
}
