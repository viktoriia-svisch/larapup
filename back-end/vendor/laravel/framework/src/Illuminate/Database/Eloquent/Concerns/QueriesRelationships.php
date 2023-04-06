<?php
namespace Illuminate\Database\Eloquent\Concerns;
use Closure;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
trait QueriesRelationships
{
    public function has($relation, $operator = '>=', $count = 1, $boolean = 'and', Closure $callback = null)
    {
        if (strpos($relation, '.') !== false) {
            return $this->hasNested($relation, $operator, $count, $boolean, $callback);
        }
        $relation = $this->getRelationWithoutConstraints($relation);
        if ($relation instanceof MorphTo) {
            throw new RuntimeException('has() and whereHas() do not support MorphTo relationships.');
        }
        $method = $this->canUseExistsForExistenceCheck($operator, $count)
                        ? 'getRelationExistenceQuery'
                        : 'getRelationExistenceCountQuery';
        $hasQuery = $relation->{$method}(
            $relation->getRelated()->newQueryWithoutRelationships(), $this
        );
        if ($callback) {
            $hasQuery->callScope($callback);
        }
        return $this->addHasWhere(
            $hasQuery, $relation, $operator, $count, $boolean
        );
    }
    protected function hasNested($relations, $operator = '>=', $count = 1, $boolean = 'and', $callback = null)
    {
        $relations = explode('.', $relations);
        $doesntHave = $operator === '<' && $count === 1;
        if ($doesntHave) {
            $operator = '>=';
            $count = 1;
        }
        $closure = function ($q) use (&$closure, &$relations, $operator, $count, $callback) {
            count($relations) > 1
                ? $q->whereHas(array_shift($relations), $closure)
                : $q->has(array_shift($relations), $operator, $count, 'and', $callback);
        };
        return $this->has(array_shift($relations), $doesntHave ? '<' : '>=', 1, $boolean, $closure);
    }
    public function orHas($relation, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or');
    }
    public function doesntHave($relation, $boolean = 'and', Closure $callback = null)
    {
        return $this->has($relation, '<', 1, $boolean, $callback);
    }
    public function orDoesntHave($relation)
    {
        return $this->doesntHave($relation, 'or');
    }
    public function whereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'and', $callback);
    }
    public function orWhereHas($relation, Closure $callback = null, $operator = '>=', $count = 1)
    {
        return $this->has($relation, $operator, $count, 'or', $callback);
    }
    public function whereDoesntHave($relation, Closure $callback = null)
    {
        return $this->doesntHave($relation, 'and', $callback);
    }
    public function orWhereDoesntHave($relation, Closure $callback = null)
    {
        return $this->doesntHave($relation, 'or', $callback);
    }
    public function withCount($relations)
    {
        if (empty($relations)) {
            return $this;
        }
        if (is_null($this->query->columns)) {
            $this->query->select([$this->query->from.'.*']);
        }
        $relations = is_array($relations) ? $relations : func_get_args();
        foreach ($this->parseWithRelations($relations) as $name => $constraints) {
            $segments = explode(' ', $name);
            unset($alias);
            if (count($segments) === 3 && Str::lower($segments[1]) === 'as') {
                [$name, $alias] = [$segments[0], $segments[2]];
            }
            $relation = $this->getRelationWithoutConstraints($name);
            $query = $relation->getRelationExistenceCountQuery(
                $relation->getRelated()->newQuery(), $this
            );
            $query->callScope($constraints);
            $query = $query->mergeConstraintsFrom($relation->getQuery())->toBase();
            if (count($query->columns) > 1) {
                $query->columns = [$query->columns[0]];
            }
            $column = $alias ?? Str::snake($name.'_count');
            $this->selectSub($query, $column);
        }
        return $this;
    }
    protected function addHasWhere(Builder $hasQuery, Relation $relation, $operator, $count, $boolean)
    {
        $hasQuery->mergeConstraintsFrom($relation->getQuery());
        return $this->canUseExistsForExistenceCheck($operator, $count)
                ? $this->addWhereExistsQuery($hasQuery->toBase(), $boolean, $operator === '<' && $count === 1)
                : $this->addWhereCountQuery($hasQuery->toBase(), $operator, $count, $boolean);
    }
    public function mergeConstraintsFrom(Builder $from)
    {
        $whereBindings = $from->getQuery()->getRawBindings()['where'] ?? [];
        return $this->withoutGlobalScopes(
            $from->removedScopes()
        )->mergeWheres(
            $from->getQuery()->wheres, $whereBindings
        );
    }
    protected function addWhereCountQuery(QueryBuilder $query, $operator = '>=', $count = 1, $boolean = 'and')
    {
        $this->query->addBinding($query->getBindings(), 'where');
        return $this->where(
            new Expression('('.$query->toSql().')'),
            $operator,
            is_numeric($count) ? new Expression($count) : $count,
            $boolean
        );
    }
    protected function getRelationWithoutConstraints($relation)
    {
        return Relation::noConstraints(function () use ($relation) {
            return $this->getModel()->{$relation}();
        });
    }
    protected function canUseExistsForExistenceCheck($operator, $count)
    {
        return ($operator === '>=' || $operator === '<') && $count === 1;
    }
}
