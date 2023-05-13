<?php
namespace Illuminate\Database\Query\Grammars;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Database\Query\Builder;
class PostgresGrammar extends Grammar
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
        'like', 'not like', 'between', 'ilike', 'not ilike',
        '~', '&', '|', '#', '<<', '>>', '<<=', '>>=',
        '&&', '@>', '<@', '?', '?|', '?&', '||', '-', '-', '#-',
        'is distinct from', 'is not distinct from',
    ];
    protected function whereBasic(Builder $query, $where)
    {
        if (Str::contains(strtolower($where['operator']), 'like')) {
            return sprintf(
                '%s::text %s %s',
                $this->wrap($where['column']),
                $where['operator'],
                $this->parameter($where['value'])
            );
        }
        return parent::whereBasic($query, $where);
    }
    protected function whereDate(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        return $this->wrap($where['column']).'::date '.$where['operator'].' '.$value;
    }
    protected function whereTime(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        return $this->wrap($where['column']).'::time '.$where['operator'].' '.$value;
    }
    protected function dateBasedWhere($type, Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        return 'extract('.$type.' from '.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }
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
    protected function compileUnion(array $union)
    {
        $conjunction = $union['all'] ? ' union all ' : ' union ';
        return $conjunction.'('.$union['query']->toSql().')';
    }
    protected function compileJsonContains($column, $value)
    {
        $column = str_replace('->>', '->', $this->wrap($column));
        return '('.$column.')::jsonb @> '.$value;
    }
    protected function compileJsonLength($column, $operator, $value)
    {
        $column = str_replace('->>', '->', $this->wrap($column));
        return 'json_array_length(('.$column.')::json) '.$operator.' '.$value;
    }
    protected function compileLock(Builder $query, $value)
    {
        if (! is_string($value)) {
            return $value ? 'for update' : 'for share';
        }
        return $value;
    }
    public function compileInsert(Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);
        return empty($values)
                ? "insert into {$table} DEFAULT VALUES"
                : parent::compileInsert($query, $values);
    }
    public function compileInsertGetId(Builder $query, $values, $sequence)
    {
        if (is_null($sequence)) {
            $sequence = 'id';
        }
        return $this->compileInsert($query, $values).' returning '.$this->wrap($sequence);
    }
    public function compileUpdate(Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);
        $columns = $this->compileUpdateColumns($values);
        $from = $this->compileUpdateFrom($query);
        $where = $this->compileUpdateWheres($query);
        return trim("update {$table} set {$columns}{$from} {$where}");
    }
    protected function compileUpdateColumns($values)
    {
        return collect($values)->map(function ($value, $key) {
            if ($this->isJsonSelector($key)) {
                return $this->compileJsonUpdateColumn($key, $value);
            }
            return $this->wrap($key).' = '.$this->parameter($value);
        })->implode(', ');
    }
    protected function compileJsonUpdateColumn($key, $value)
    {
        $parts = explode('->', $key);
        $field = $this->wrap(array_shift($parts));
        $path = '\'{"'.implode('","', $parts).'"}\'';
        return "{$field} = jsonb_set({$field}::jsonb, {$path}, {$this->parameter($value)})";
    }
    protected function compileUpdateFrom(Builder $query)
    {
        if (! isset($query->joins)) {
            return '';
        }
        $froms = collect($query->joins)->map(function ($join) {
            return $this->wrapTable($join->table);
        })->all();
        if (count($froms) > 0) {
            return ' from '.implode(', ', $froms);
        }
    }
    protected function compileUpdateWheres(Builder $query)
    {
        $baseWheres = $this->compileWheres($query);
        if (! isset($query->joins)) {
            return $baseWheres;
        }
        $joinWheres = $this->compileUpdateJoinWheres($query);
        if (trim($baseWheres) == '') {
            return 'where '.$this->removeLeadingBoolean($joinWheres);
        }
        return $baseWheres.' '.$joinWheres;
    }
    protected function compileUpdateJoinWheres(Builder $query)
    {
        $joinWheres = [];
        foreach ($query->joins as $join) {
            foreach ($join->wheres as $where) {
                $method = "where{$where['type']}";
                $joinWheres[] = $where['boolean'].' '.$this->$method($query, $where);
            }
        }
        return implode(' ', $joinWheres);
    }
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $values = collect($values)->map(function ($value, $column) {
            return $this->isJsonSelector($column) && ! $this->isExpression($value)
                ? json_encode($value)
                : $value;
        })->all();
        $bindingsWithoutJoin = Arr::except($bindings, 'join');
        return array_values(
            array_merge($values, $bindings['join'], Arr::flatten($bindingsWithoutJoin))
        );
    }
    public function compileDelete(Builder $query)
    {
        $table = $this->wrapTable($query->from);
        return isset($query->joins)
            ? $this->compileDeleteWithJoins($query, $table)
            : parent::compileDelete($query);
    }
    protected function compileDeleteWithJoins($query, $table)
    {
        $using = ' USING '.collect($query->joins)->map(function ($join) {
            return $this->wrapTable($join->table);
        })->implode(', ');
        $where = count($query->wheres) > 0 ? ' '.$this->compileUpdateWheres($query) : '';
        return trim("delete from {$table}{$using}{$where}");
    }
    public function compileTruncate(Builder $query)
    {
        return ['truncate '.$this->wrapTable($query->from).' restart identity cascade' => []];
    }
    protected function wrapJsonSelector($value)
    {
        $path = explode('->', $value);
        $field = $this->wrapSegments(explode('.', array_shift($path)));
        $wrappedPath = $this->wrapJsonPathAttributes($path);
        $attribute = array_pop($wrappedPath);
        if (! empty($wrappedPath)) {
            return $field.'->'.implode('->', $wrappedPath).'->>'.$attribute;
        }
        return $field.'->>'.$attribute;
    }
    protected function wrapJsonPathAttributes($path)
    {
        return array_map(function ($attribute) {
            return "'$attribute'";
        }, $path);
    }
}
