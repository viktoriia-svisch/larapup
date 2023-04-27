<?php
namespace Illuminate\Database\Query\Grammars;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JsonExpression;
class MySqlGrammar extends Grammar
{
    protected $operators = ['sounds like'];
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
    public function compileSelect(Builder $query)
    {
        if ($query->unions && $query->aggregate) {
            return $this->compileUnionAggregate($query);
        }
        $sql = parent::compileSelect($query);
        if ($query->unions) {
            $sql = '('.$sql.') '.$this->compileUnions($query);
        }
        return $sql;
    }
    protected function compileJsonContains($column, $value)
    {
        return 'json_contains('.$this->wrap($column).', '.$value.')';
    }
    protected function compileJsonLength($column, $operator, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);
        return 'json_length('.$field.$path.') '.$operator.' '.$value;
    }
    protected function compileUnion(array $union)
    {
        $conjunction = $union['all'] ? ' union all ' : ' union ';
        return $conjunction.'('.$union['query']->toSql().')';
    }
    public function compileRandom($seed)
    {
        return 'RAND('.$seed.')';
    }
    protected function compileLock(Builder $query, $value)
    {
        if (! is_string($value)) {
            return $value ? 'for update' : 'lock in share mode';
        }
        return $value;
    }
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);
        $columns = $this->compileUpdateColumns($values);
        $joins = '';
        if (isset($query->joins)) {
            $joins = ' '.$this->compileJoins($query, $query->joins);
        }
        $where = $this->compileWheres($query);
        $sql = rtrim("update {$table}{$joins} set $columns $where");
        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }
        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }
        return rtrim($sql);
    }
    protected function compileUpdateColumns($values)
    {
        return collect($values)->map(function ($value, $key) {
            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($key, new JsonExpression($value));
            }
            return $this->wrap($key).' = '.$this->parameter($value);
        })->implode(', ');
    }
    protected function compileJsonUpdateColumn($key, JsonExpression $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($key);
        return "{$field} = json_set({$field}{$path}, {$value->getValue()})";
    }
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $values = collect($values)->reject(function ($value, $column) {
            return $this->isJsonSelector($column) && is_bool($value);
        })->all();
        return parent::prepareBindingsForUpdate($bindings, $values);
    }
    public function compileDelete(Builder $query)
    {
        $table = $this->wrapTable($query->from);
        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';
        return isset($query->joins)
                    ? $this->compileDeleteWithJoins($query, $table, $where)
                    : $this->compileDeleteWithoutJoins($query, $table, $where);
    }
    public function prepareBindingsForDelete(array $bindings)
    {
        $cleanBindings = Arr::except($bindings, ['join', 'select']);
        return array_values(
            array_merge($bindings['join'], Arr::flatten($cleanBindings))
        );
    }
    protected function compileDeleteWithoutJoins($query, $table, $where)
    {
        $sql = trim("delete from {$table} {$where}");
        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }
        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }
        return $sql;
    }
    protected function compileDeleteWithJoins($query, $table, $where)
    {
        $joins = ' '.$this->compileJoins($query, $query->joins);
        $alias = stripos($table, ' as ') !== false
                ? explode(' as ', $table)[1] : $table;
        return trim("delete {$alias} from {$table}{$joins} {$where}");
    }
    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '`'.str_replace('`', '``', $value).'`';
    }
    protected function wrapJsonSelector($value)
    {
        $delimiter = Str::contains($value, '->>')
            ? '->>'
            : '->';
        $path = explode($delimiter, $value);
        $field = $this->wrapSegments(explode('.', array_shift($path)));
        return sprintf('%s'.$delimiter.'\'$.%s\'', $field, collect($path)->map(function ($part) {
            return '"'.$part.'"';
        })->implode('.'));
    }
}
