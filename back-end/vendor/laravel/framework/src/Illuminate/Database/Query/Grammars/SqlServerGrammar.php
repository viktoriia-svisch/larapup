<?php
namespace Illuminate\Database\Query\Grammars;
use Illuminate\Support\Arr;
use Illuminate\Database\Query\Builder;
class SqlServerGrammar extends Grammar
{
    protected $operators = [
        '=', '<', '>', '<=', '>=', '!<', '!>', '<>', '!=',
        'like', 'not like', 'ilike',
        '&', '&=', '|', '|=', '^', '^=',
    ];
    public function compileSelect(Builder $query)
    {
        if (! $query->offset) {
            return parent::compileSelect($query);
        }
        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }
        return $this->compileAnsiOffset(
            $query, $this->compileComponents($query)
        );
    }
    protected function compileColumns(Builder $query, $columns)
    {
        if (! is_null($query->aggregate)) {
            return;
        }
        $select = $query->distinct ? 'select distinct ' : 'select ';
        if ($query->limit > 0 && $query->offset <= 0) {
            $select .= 'top '.$query->limit.' ';
        }
        return $select.$this->columnize($columns);
    }
    protected function compileFrom(Builder $query, $table)
    {
        $from = parent::compileFrom($query, $table);
        if (is_string($query->lock)) {
            return $from.' '.$query->lock;
        }
        if (! is_null($query->lock)) {
            return $from.' with(rowlock,'.($query->lock ? 'updlock,' : '').'holdlock)';
        }
        return $from;
    }
    protected function whereDate(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        return 'cast('.$this->wrap($where['column']).' as date) '.$where['operator'].' '.$value;
    }
    protected function whereTime(Builder $query, $where)
    {
        $value = $this->parameter($where['value']);
        return 'cast('.$this->wrap($where['column']).' as time) '.$where['operator'].' '.$value;
    }
    protected function compileJsonContains($column, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);
        return $value.' in (select [value] from openjson('.$field.$path.'))';
    }
    public function prepareBindingForJsonContains($binding)
    {
        return is_bool($binding) ? json_encode($binding) : $binding;
    }
    protected function compileJsonLength($column, $operator, $value)
    {
        [$field, $path] = $this->wrapJsonFieldAndPath($column);
        return '(select count(*) from openjson('.$field.$path.')) '.$operator.' '.$value;
    }
    protected function compileAnsiOffset(Builder $query, $components)
    {
        if (empty($components['orders'])) {
            $components['orders'] = 'order by (select 0)';
        }
        $components['columns'] .= $this->compileOver($components['orders']);
        unset($components['orders']);
        $sql = $this->concatenate($components);
        return $this->compileTableExpression($sql, $query);
    }
    protected function compileOver($orderings)
    {
        return ", row_number() over ({$orderings}) as row_num";
    }
    protected function compileTableExpression($sql, $query)
    {
        $constraint = $this->compileRowConstraint($query);
        return "select * from ({$sql}) as temp_table where row_num {$constraint} order by row_num";
    }
    protected function compileRowConstraint($query)
    {
        $start = $query->offset + 1;
        if ($query->limit > 0) {
            $finish = $query->offset + $query->limit;
            return "between {$start} and {$finish}";
        }
        return ">= {$start}";
    }
    public function compileRandom($seed)
    {
        return 'NEWID()';
    }
    protected function compileLimit(Builder $query, $limit)
    {
        return '';
    }
    protected function compileOffset(Builder $query, $offset)
    {
        return '';
    }
    protected function compileLock(Builder $query, $value)
    {
        return '';
    }
    public function compileExists(Builder $query)
    {
        $existsQuery = clone $query;
        $existsQuery->columns = [];
        return $this->compileSelect($existsQuery->selectRaw('1 [exists]')->limit(1));
    }
    public function compileDelete(Builder $query)
    {
        $table = $this->wrapTable($query->from);
        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';
        return isset($query->joins)
                    ? $this->compileDeleteWithJoins($query, $table, $where)
                    : trim("delete from {$table} {$where}");
    }
    protected function compileDeleteWithJoins(Builder $query, $table, $where)
    {
        $joins = ' '.$this->compileJoins($query, $query->joins);
        $alias = stripos($table, ' as ') !== false
                ? explode(' as ', $table)[1] : $table;
        return trim("delete {$alias} from {$table}{$joins} {$where}");
    }
    public function compileTruncate(Builder $query)
    {
        return ['truncate table '.$this->wrapTable($query->from) => []];
    }
    public function compileUpdate(Builder $query, $values)
    {
        [$table, $alias] = $this->parseUpdateTable($query->from);
        $columns = collect($values)->map(function ($value, $key) {
            return $this->wrap($key).' = '.$this->parameter($value);
        })->implode(', ');
        $joins = '';
        if (isset($query->joins)) {
            $joins = ' '.$this->compileJoins($query, $query->joins);
        }
        $where = $this->compileWheres($query);
        if (! empty($joins)) {
            return trim("update {$alias} set {$columns} from {$table}{$joins} {$where}");
        }
        return trim("update {$table}{$joins} set $columns $where");
    }
    protected function parseUpdateTable($table)
    {
        $table = $alias = $this->wrapTable($table);
        if (stripos($table, '] as [') !== false) {
            $alias = '['.explode('] as [', $table)[1];
        }
        return [$table, $alias];
    }
    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $bindingsWithoutJoin = Arr::except($bindings, 'join');
        return array_values(
            array_merge($values, $bindings['join'], Arr::flatten($bindingsWithoutJoin))
        );
    }
    public function compileSavepoint($name)
    {
        return 'SAVE TRANSACTION '.$name;
    }
    public function compileSavepointRollBack($name)
    {
        return 'ROLLBACK TRANSACTION '.$name;
    }
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s.v';
    }
    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '['.str_replace(']', ']]', $value).']';
    }
    protected function wrapJsonSelector($value)
    {
        $parts = explode('->', $value, 2);
        $field = $this->wrapSegments(explode('.', array_shift($parts)));
        return 'json_value('.$field.', '.$this->wrapJsonPath($parts[0]).')';
    }
    public function wrapTable($table)
    {
        if (! $this->isExpression($table)) {
            return $this->wrapTableValuedFunction(parent::wrapTable($table));
        }
        return $this->getValue($table);
    }
    protected function wrapTableValuedFunction($table)
    {
        if (preg_match('/^(.+?)(\(.*?\))]$/', $table, $matches) === 1) {
            $table = $matches[1].']'.$matches[2];
        }
        return $table;
    }
}
