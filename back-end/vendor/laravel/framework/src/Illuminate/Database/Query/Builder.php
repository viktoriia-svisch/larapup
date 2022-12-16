<?php
namespace Illuminate\Database\Query;
use Closure;
use RuntimeException;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
class Builder
{
    use BuildsQueries, ForwardsCalls, Macroable {
        __call as macroCall;
    }
    public $connection;
    public $grammar;
    public $processor;
    public $bindings = [
        'select' => [],
        'from'   => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => [],
        'union'  => [],
    ];
    public $aggregate;
    public $columns;
    public $distinct = false;
    public $from;
    public $joins;
    public $wheres = [];
    public $groups;
    public $havings;
    public $orders;
    public $limit;
    public $offset;
    public $unions;
    public $unionLimit;
    public $unionOffset;
    public $unionOrders;
    public $lock;
    public $operators = [
        '=', '<', '>', '<=', '>=', '<>', '!=', '<=>',
        'like', 'like binary', 'not like', 'ilike',
        '&', '|', '^', '<<', '>>',
        'rlike', 'regexp', 'not regexp',
        '~', '~*', '!~', '!~*', 'similar to',
        'not similar to', 'not ilike', '~~*', '!~~*',
    ];
    public $useWritePdo = false;
    public function __construct(ConnectionInterface $connection,
                                Grammar $grammar = null,
                                Processor $processor = null)
    {
        $this->connection = $connection;
        $this->grammar = $grammar ?: $connection->getQueryGrammar();
        $this->processor = $processor ?: $connection->getPostProcessor();
    }
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }
    public function selectSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);
        return $this->selectRaw(
            '('.$query.') as '.$this->grammar->wrap($as), $bindings
        );
    }
    public function selectRaw($expression, array $bindings = [])
    {
        $this->addSelect(new Expression($expression));
        if ($bindings) {
            $this->addBinding($bindings, 'select');
        }
        return $this;
    }
    public function fromSub($query, $as)
    {
        [$query, $bindings] = $this->createSub($query);
        return $this->fromRaw('('.$query.') as '.$this->grammar->wrap($as), $bindings);
    }
    public function fromRaw($expression, $bindings = [])
    {
        $this->from = new Expression($expression);
        $this->addBinding($bindings, 'from');
        return $this;
    }
    protected function createSub($query)
    {
        if ($query instanceof Closure) {
            $callback = $query;
            $callback($query = $this->forSubQuery());
        }
        return $this->parseSub($query);
    }
    protected function parseSub($query)
    {
        if ($query instanceof self || $query instanceof EloquentBuilder) {
            return [$query->toSql(), $query->getBindings()];
        } elseif (is_string($query)) {
            return [$query, []];
        } else {
            throw new InvalidArgumentException;
        }
    }
    public function addSelect($column)
    {
        $column = is_array($column) ? $column : func_get_args();
        $this->columns = array_merge((array) $this->columns, $column);
        return $this;
    }
    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }
    public function from($table)
    {
        $this->from = $table;
        return $this;
    }
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $join = $this->newJoinClause($this, $type, $table);
        if ($first instanceof Closure) {
            call_user_func($first, $join);
            $this->joins[] = $join;
            $this->addBinding($join->getBindings(), 'join');
        }
        else {
            $method = $where ? 'where' : 'on';
            $this->joins[] = $join->$method($first, $operator, $second);
            $this->addBinding($join->getBindings(), 'join');
        }
        return $this;
    }
    public function joinWhere($table, $first, $operator, $second, $type = 'inner')
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }
    public function joinSub($query, $as, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        [$query, $bindings] = $this->createSub($query);
        $expression = '('.$query.') as '.$this->grammar->wrap($as);
        $this->addBinding($bindings, 'join');
        return $this->join(new Expression($expression), $first, $operator, $second, $type, $where);
    }
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }
    public function leftJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'left');
    }
    public function leftJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return $this->joinSub($query, $as, $first, $operator, $second, 'left');
    }
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }
    public function rightJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'right');
    }
    public function rightJoinSub($query, $as, $first, $operator = null, $second = null)
    {
        return $this->joinSub($query, $as, $first, $operator, $second, 'right');
    }
    public function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        if ($first) {
            return $this->join($table, $first, $operator, $second, 'cross');
        }
        $this->joins[] = $this->newJoinClause($this, 'cross', $table);
        return $this;
    }
    protected function newJoinClause(self $parentQuery, $type, $table)
    {
        return new JoinClause($parentQuery, $type, $table);
    }
    public function mergeWheres($wheres, $bindings)
    {
        $this->wheres = array_merge($this->wheres, (array) $wheres);
        $this->bindings['where'] = array_values(
            array_merge($this->bindings['where'], (array) $bindings)
        );
    }
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($column instanceof Closure) {
            return $this->whereNested($column, $boolean);
        }
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }
        if ($value instanceof Closure) {
            return $this->whereSub($column, $operator, $value, $boolean);
        }
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }
        if (Str::contains($column, '->') && is_bool($value)) {
            $value = new Expression($value ? 'true' : 'false');
        }
        $type = 'Basic';
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );
        if (! $value instanceof Expression) {
            $this->addBinding($value, 'where');
        }
        return $this;
    }
    protected function addArrayOfWheres($column, $boolean, $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $method, $boolean) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    $query->{$method}(...array_values($value));
                } else {
                    $query->$method($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }
    public function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }
        return [$value, $operator];
    }
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
             ! in_array($operator, ['=', '<>', '!=']);
    }
    protected function invalidOperator($operator)
    {
        return ! in_array(strtolower($operator), $this->operators, true) &&
               ! in_array(strtolower($operator), $this->grammar->getOperators(), true);
    }
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->where($column, $operator, $value, 'or');
    }
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        if (is_array($first)) {
            return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
        }
        if ($this->invalidOperator($operator)) {
            [$second, $operator] = [$operator, '='];
        }
        $type = 'Column';
        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );
        return $this;
    }
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];
        $this->addBinding((array) $bindings, 'where');
        return $this;
    }
    public function orWhereRaw($sql, $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';
        if ($values instanceof EloquentBuilder) {
            $values = $values->getQuery();
        }
        if ($values instanceof self) {
            return $this->whereInExistingQuery(
                $column, $values, $boolean, $not
            );
        }
        if ($values instanceof Closure) {
            return $this->whereInSub($column, $values, $boolean, $not);
        }
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        $this->addBinding($this->cleanBindings($values), 'where');
        return $this;
    }
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }
    protected function whereInSub($column, Closure $callback, $boolean, $not)
    {
        $type = $not ? 'NotInSub' : 'InSub';
        call_user_func($callback, $query = $this->forSubQuery());
        $this->wheres[] = compact('type', 'column', 'query', 'boolean');
        $this->addBinding($query->getBindings(), 'where');
        return $this;
    }
    protected function whereInExistingQuery($column, $query, $boolean, $not)
    {
        $type = $not ? 'NotInSub' : 'InSub';
        $this->wheres[] = compact('type', 'column', 'query', 'boolean');
        $this->addBinding($query->getBindings(), 'where');
        return $this;
    }
    public function whereIntegerInRaw($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotInRaw' : 'InRaw';
        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }
        foreach ($values as &$value) {
            $value = (int) $value;
        }
        $this->wheres[] = compact('type', 'column', 'values', 'boolean');
        return $this;
    }
    public function whereIntegerNotInRaw($column, $values, $boolean = 'and')
    {
        return $this->whereIntegerInRaw($column, $values, $boolean, true);
    }
    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';
        $this->wheres[] = compact('type', 'column', 'boolean');
        return $this;
    }
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }
    public function whereNotNull($column, $boolean = 'and')
    {
        return $this->whereNull($column, $boolean, true);
    }
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';
        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');
        $this->addBinding($this->cleanBindings($values), 'where');
        return $this;
    }
    public function orWhereBetween($column, array $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }
    public function whereNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }
    public function orWhereNotBetween($column, array $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }
    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }
        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }
    public function orWhereDate($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->whereDate($column, $operator, $value, 'or');
    }
    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i:s');
        }
        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }
    public function orWhereTime($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->whereTime($column, $operator, $value, 'or');
    }
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('d');
        }
        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }
    public function orWhereDay($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->addDateBasedWhere('Day', $column, $operator, $value, 'or');
    }
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('m');
        }
        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }
    public function orWhereMonth($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->addDateBasedWhere('Month', $column, $operator, $value, 'or');
    }
    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y');
        }
        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }
    public function orWhereYear($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->addDateBasedWhere('Year', $column, $operator, $value, 'or');
    }
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');
        if (! $value instanceof Expression) {
            $this->addBinding($value, 'where');
        }
        return $this;
    }
    public function whereNested(Closure $callback, $boolean = 'and')
    {
        call_user_func($callback, $query = $this->forNestedWhere());
        return $this->addNestedWhereQuery($query, $boolean);
    }
    public function forNestedWhere()
    {
        return $this->newQuery()->from($this->from);
    }
    public function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';
            $this->wheres[] = compact('type', 'query', 'boolean');
            $this->addBinding($query->getRawBindings()['where'], 'where');
        }
        return $this;
    }
    protected function whereSub($column, $operator, Closure $callback, $boolean)
    {
        $type = 'Sub';
        call_user_func($callback, $query = $this->forSubQuery());
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'query', 'boolean'
        );
        $this->addBinding($query->getBindings(), 'where');
        return $this;
    }
    public function whereExists(Closure $callback, $boolean = 'and', $not = false)
    {
        $query = $this->forSubQuery();
        call_user_func($callback, $query);
        return $this->addWhereExistsQuery($query, $boolean, $not);
    }
    public function orWhereExists(Closure $callback, $not = false)
    {
        return $this->whereExists($callback, 'or', $not);
    }
    public function whereNotExists(Closure $callback, $boolean = 'and')
    {
        return $this->whereExists($callback, $boolean, true);
    }
    public function orWhereNotExists(Closure $callback)
    {
        return $this->orWhereExists($callback, true);
    }
    public function addWhereExistsQuery(self $query, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotExists' : 'Exists';
        $this->wheres[] = compact('type', 'query', 'boolean');
        $this->addBinding($query->getBindings(), 'where');
        return $this;
    }
    public function whereRowValues($columns, $operator, $values, $boolean = 'and')
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException('The number of columns must match the number of values');
        }
        $type = 'RowValues';
        $this->wheres[] = compact('type', 'columns', 'operator', 'values', 'boolean');
        $this->addBinding($this->cleanBindings($values));
        return $this;
    }
    public function orWhereRowValues($columns, $operator, $values)
    {
        return $this->whereRowValues($columns, $operator, $values, 'or');
    }
    public function whereJsonContains($column, $value, $boolean = 'and', $not = false)
    {
        $type = 'JsonContains';
        $this->wheres[] = compact('type', 'column', 'value', 'boolean', 'not');
        if (! $value instanceof Expression) {
            $this->addBinding($this->grammar->prepareBindingForJsonContains($value));
        }
        return $this;
    }
    public function orWhereJsonContains($column, $value)
    {
        return $this->whereJsonContains($column, $value, 'or');
    }
    public function whereJsonDoesntContain($column, $value, $boolean = 'and')
    {
        return $this->whereJsonContains($column, $value, $boolean, true);
    }
    public function orWhereJsonDoesntContain($column, $value)
    {
        return $this->whereJsonDoesntContain($column, $value, 'or');
    }
    public function whereJsonLength($column, $operator, $value = null, $boolean = 'and')
    {
        $type = 'JsonLength';
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');
        if (! $value instanceof Expression) {
            $this->addBinding($value);
        }
        return $this;
    }
    public function orWhereJsonLength($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->whereJsonLength($column, $operator, $value, 'or');
    }
    public function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);
        $segments = preg_split(
            '/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE
        );
        $connector = 'and';
        $index = 0;
        foreach ($segments as $segment) {
            if ($segment !== 'And' && $segment !== 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);
                $index++;
            }
            else {
                $connector = $segment;
            }
        }
        return $this;
    }
    protected function addDynamic($segment, $connector, $parameters, $index)
    {
        $bool = strtolower($connector);
        $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
    }
    public function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Arr::wrap($group)
            );
        }
        return $this;
    }
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Basic';
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$operator, '='];
        }
        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');
        if (! $value instanceof Expression) {
            $this->addBinding($value, 'having');
        }
        return $this;
    }
    public function orHaving($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );
        return $this->having($column, $operator, $value, 'or');
    }
    public function havingBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';
        $this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');
        $this->addBinding($this->cleanBindings($values), 'having');
        return $this;
    }
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'Raw';
        $this->havings[] = compact('type', 'sql', 'boolean');
        $this->addBinding($bindings, 'having');
        return $this;
    }
    public function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }
    public function orderBy($column, $direction = 'asc')
    {
        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];
        return $this;
    }
    public function orderByDesc($column)
    {
        return $this->orderBy($column, 'desc');
    }
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }
    public function oldest($column = 'created_at')
    {
        return $this->orderBy($column, 'asc');
    }
    public function inRandomOrder($seed = '')
    {
        return $this->orderByRaw($this->grammar->compileRandom($seed));
    }
    public function orderByRaw($sql, $bindings = [])
    {
        $type = 'Raw';
        $this->{$this->unions ? 'unionOrders' : 'orders'}[] = compact('type', 'sql');
        $this->addBinding($bindings, 'order');
        return $this;
    }
    public function skip($value)
    {
        return $this->offset($value);
    }
    public function offset($value)
    {
        $property = $this->unions ? 'unionOffset' : 'offset';
        $this->$property = max(0, $value);
        return $this;
    }
    public function take($value)
    {
        return $this->limit($value);
    }
    public function limit($value)
    {
        $property = $this->unions ? 'unionLimit' : 'limit';
        if ($value >= 0) {
            $this->$property = $value;
        }
        return $this;
    }
    public function forPage($page, $perPage = 15)
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }
    public function forPageAfterId($perPage = 15, $lastId = 0, $column = 'id')
    {
        $this->orders = $this->removeExistingOrdersFor($column);
        if (! is_null($lastId)) {
            $this->where($column, '>', $lastId);
        }
        return $this->orderBy($column, 'asc')
                    ->take($perPage);
    }
    protected function removeExistingOrdersFor($column)
    {
        return Collection::make($this->orders)
                    ->reject(function ($order) use ($column) {
                        return isset($order['column'])
                               ? $order['column'] === $column : false;
                    })->values()->all();
    }
    public function union($query, $all = false)
    {
        if ($query instanceof Closure) {
            call_user_func($query, $query = $this->newQuery());
        }
        $this->unions[] = compact('query', 'all');
        $this->addBinding($query->getBindings(), 'union');
        return $this;
    }
    public function unionAll($query)
    {
        return $this->union($query, true);
    }
    public function lock($value = true)
    {
        $this->lock = $value;
        if (! is_null($this->lock)) {
            $this->useWritePdo();
        }
        return $this;
    }
    public function lockForUpdate()
    {
        return $this->lock(true);
    }
    public function sharedLock()
    {
        return $this->lock(false);
    }
    public function toSql()
    {
        return $this->grammar->compileSelect($this);
    }
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }
    public function value($column)
    {
        $result = (array) $this->first([$column]);
        return count($result) > 0 ? reset($result) : null;
    }
    public function get($columns = ['*'])
    {
        return collect($this->onceWithColumns($columns, function () {
            return $this->processor->processSelect($this, $this->runSelect());
        }));
    }
    protected function runSelect()
    {
        return $this->connection->select(
            $this->toSql(), $this->getBindings(), ! $this->useWritePdo
        );
    }
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $total = $this->getCountForPagination($columns);
        $results = $total ? $this->forPage($page, $perPage)->get($columns) : collect();
        return $this->paginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function simplePaginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);
        $this->skip(($page - 1) * $perPage)->take($perPage + 1);
        return $this->simplePaginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }
    public function getCountForPagination($columns = ['*'])
    {
        $results = $this->runPaginationCountQuery($columns);
        if (isset($this->groups)) {
            return count($results);
        } elseif (! isset($results[0])) {
            return 0;
        } elseif (is_object($results[0])) {
            return (int) $results[0]->aggregate;
        }
        return (int) array_change_key_case((array) $results[0])['aggregate'];
    }
    protected function runPaginationCountQuery($columns = ['*'])
    {
        $without = $this->unions ? ['orders', 'limit', 'offset'] : ['columns', 'orders', 'limit', 'offset'];
        return $this->cloneWithout($without)
                    ->cloneWithoutBindings($this->unions ? ['order'] : ['select', 'order'])
                    ->setAggregate('count', $this->withoutSelectAliases($columns))
                    ->get()->all();
    }
    protected function withoutSelectAliases(array $columns)
    {
        return array_map(function ($column) {
            return is_string($column) && ($aliasPosition = stripos($column, ' as ')) !== false
                    ? substr($column, 0, $aliasPosition) : $column;
        }, $columns);
    }
    public function cursor()
    {
        if (is_null($this->columns)) {
            $this->columns = ['*'];
        }
        return $this->connection->cursor(
            $this->toSql(), $this->getBindings(), ! $this->useWritePdo
        );
    }
    public function chunkById($count, callable $callback, $column = 'id', $alias = null)
    {
        $alias = $alias ?: $column;
        $lastId = null;
        do {
            $clone = clone $this;
            $results = $clone->forPageAfterId($count, $lastId, $column)->get();
            $countResults = $results->count();
            if ($countResults == 0) {
                break;
            }
            if ($callback($results) === false) {
                return false;
            }
            $lastId = $results->last()->{$alias};
            unset($results);
        } while ($countResults == $count);
        return true;
    }
    protected function enforceOrderBy()
    {
        if (empty($this->orders) && empty($this->unionOrders)) {
            throw new RuntimeException('You must specify an orderBy clause when using this function.');
        }
    }
    public function pluck($column, $key = null)
    {
        $queryResult = $this->onceWithColumns(
            is_null($key) ? [$column] : [$column, $key],
            function () {
                return $this->processor->processSelect(
                    $this, $this->runSelect()
                );
            }
        );
        if (empty($queryResult)) {
            return collect();
        }
        $column = $this->stripTableForPluck($column);
        $key = $this->stripTableForPluck($key);
        return is_array($queryResult[0])
                    ? $this->pluckFromArrayColumn($queryResult, $column, $key)
                    : $this->pluckFromObjectColumn($queryResult, $column, $key);
    }
    protected function stripTableForPluck($column)
    {
        return is_null($column) ? $column : last(preg_split('~\.| ~', $column));
    }
    protected function pluckFromObjectColumn($queryResult, $column, $key)
    {
        $results = [];
        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row->$column;
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row->$key] = $row->$column;
            }
        }
        return collect($results);
    }
    protected function pluckFromArrayColumn($queryResult, $column, $key)
    {
        $results = [];
        if (is_null($key)) {
            foreach ($queryResult as $row) {
                $results[] = $row[$column];
            }
        } else {
            foreach ($queryResult as $row) {
                $results[$row[$key]] = $row[$column];
            }
        }
        return collect($results);
    }
    public function implode($column, $glue = '')
    {
        return $this->pluck($column)->implode($glue);
    }
    public function exists()
    {
        $results = $this->connection->select(
            $this->grammar->compileExists($this), $this->getBindings(), ! $this->useWritePdo
        );
        if (isset($results[0])) {
            $results = (array) $results[0];
            return (bool) $results['exists'];
        }
        return false;
    }
    public function doesntExist()
    {
        return ! $this->exists();
    }
    public function count($columns = '*')
    {
        return (int) $this->aggregate(__FUNCTION__, Arr::wrap($columns));
    }
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }
    public function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);
        return $result ?: 0;
    }
    public function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }
    public function average($column)
    {
        return $this->avg($column);
    }
    public function aggregate($function, $columns = ['*'])
    {
        $results = $this->cloneWithout($this->unions ? [] : ['columns'])
                        ->cloneWithoutBindings($this->unions ? [] : ['select'])
                        ->setAggregate($function, $columns)
                        ->get($columns);
        if (! $results->isEmpty()) {
            return array_change_key_case((array) $results[0])['aggregate'];
        }
    }
    public function numericAggregate($function, $columns = ['*'])
    {
        $result = $this->aggregate($function, $columns);
        if (! $result) {
            return 0;
        }
        if (is_int($result) || is_float($result)) {
            return $result;
        }
        return strpos((string) $result, '.') === false
                ? (int) $result : (float) $result;
    }
    protected function setAggregate($function, $columns)
    {
        $this->aggregate = compact('function', 'columns');
        if (empty($this->groups)) {
            $this->orders = null;
            $this->bindings['order'] = [];
        }
        return $this;
    }
    protected function onceWithColumns($columns, $callback)
    {
        $original = $this->columns;
        if (is_null($original)) {
            $this->columns = $columns;
        }
        $result = $callback();
        $this->columns = $original;
        return $result;
    }
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }
        if (! is_array(reset($values))) {
            $values = [$values];
        }
        else {
            foreach ($values as $key => $value) {
                ksort($value);
                $values[$key] = $value;
            }
        }
        return $this->connection->insert(
            $this->grammar->compileInsert($this, $values),
            $this->cleanBindings(Arr::flatten($values, 1))
        );
    }
    public function insertGetId(array $values, $sequence = null)
    {
        $sql = $this->grammar->compileInsertGetId($this, $values, $sequence);
        $values = $this->cleanBindings($values);
        return $this->processor->processInsertGetId($this, $sql, $values, $sequence);
    }
    public function insertUsing(array $columns, $query)
    {
        [$sql, $bindings] = $this->createSub($query);
        return $this->connection->insert(
            $this->grammar->compileInsertUsing($this, $columns, $sql),
            $this->cleanBindings($bindings)
        );
    }
    public function update(array $values)
    {
        $sql = $this->grammar->compileUpdate($this, $values);
        return $this->connection->update($sql, $this->cleanBindings(
            $this->grammar->prepareBindingsForUpdate($this->bindings, $values)
        ));
    }
    public function updateOrInsert(array $attributes, array $values = [])
    {
        if (! $this->where($attributes)->exists()) {
            return $this->insert(array_merge($attributes, $values));
        }
        return (bool) $this->take(1)->update($values);
    }
    public function increment($column, $amount = 1, array $extra = [])
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to increment method.');
        }
        $wrapped = $this->grammar->wrap($column);
        $columns = array_merge([$column => $this->raw("$wrapped + $amount")], $extra);
        return $this->update($columns);
    }
    public function decrement($column, $amount = 1, array $extra = [])
    {
        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Non-numeric value passed to decrement method.');
        }
        $wrapped = $this->grammar->wrap($column);
        $columns = array_merge([$column => $this->raw("$wrapped - $amount")], $extra);
        return $this->update($columns);
    }
    public function delete($id = null)
    {
        if (! is_null($id)) {
            $this->where($this->from.'.id', '=', $id);
        }
        return $this->connection->delete(
            $this->grammar->compileDelete($this), $this->cleanBindings(
                $this->grammar->prepareBindingsForDelete($this->bindings)
            )
        );
    }
    public function truncate()
    {
        foreach ($this->grammar->compileTruncate($this) as $sql => $bindings) {
            $this->connection->statement($sql, $bindings);
        }
    }
    public function newQuery()
    {
        return new static($this->connection, $this->grammar, $this->processor);
    }
    protected function forSubQuery()
    {
        return $this->newQuery();
    }
    public function raw($value)
    {
        return $this->connection->raw($value);
    }
    public function getBindings()
    {
        return Arr::flatten($this->bindings);
    }
    public function getRawBindings()
    {
        return $this->bindings;
    }
    public function setBindings(array $bindings, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }
        $this->bindings[$type] = $bindings;
        return $this;
    }
    public function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }
        if (is_array($value)) {
            $this->bindings[$type] = array_values(array_merge($this->bindings[$type], $value));
        } else {
            $this->bindings[$type][] = $value;
        }
        return $this;
    }
    public function mergeBindings(self $query)
    {
        $this->bindings = array_merge_recursive($this->bindings, $query->bindings);
        return $this;
    }
    protected function cleanBindings(array $bindings)
    {
        return array_values(array_filter($bindings, function ($binding) {
            return ! $binding instanceof Expression;
        }));
    }
    public function getConnection()
    {
        return $this->connection;
    }
    public function getProcessor()
    {
        return $this->processor;
    }
    public function getGrammar()
    {
        return $this->grammar;
    }
    public function useWritePdo()
    {
        $this->useWritePdo = true;
        return $this;
    }
    public function cloneWithout(array $properties)
    {
        return tap(clone $this, function ($clone) use ($properties) {
            foreach ($properties as $property) {
                $clone->{$property} = null;
            }
        });
    }
    public function cloneWithoutBindings(array $except)
    {
        return tap(clone $this, function ($clone) use ($except) {
            foreach ($except as $type) {
                $clone->bindings[$type] = [];
            }
        });
    }
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }
        if (Str::startsWith($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }
        static::throwBadMethodCallException($method);
    }
}
