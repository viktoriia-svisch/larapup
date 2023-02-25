<?php
namespace Illuminate\Database\Query;
use Closure;
class JoinClause extends Builder
{
    public $type;
    public $table;
    private $parentQuery;
    public function __construct(Builder $parentQuery, $type, $table)
    {
        $this->type = $type;
        $this->table = $table;
        $this->parentQuery = $parentQuery;
        parent::__construct(
            $parentQuery->getConnection(), $parentQuery->getGrammar(), $parentQuery->getProcessor()
        );
    }
    public function on($first, $operator = null, $second = null, $boolean = 'and')
    {
        if ($first instanceof Closure) {
            return $this->whereNested($first, $boolean);
        }
        return $this->whereColumn($first, $operator, $second, $boolean);
    }
    public function orOn($first, $operator = null, $second = null)
    {
        return $this->on($first, $operator, $second, 'or');
    }
    public function newQuery()
    {
        return new static($this->parentQuery, $this->type, $this->table);
    }
    protected function forSubQuery()
    {
        return $this->parentQuery->newQuery();
    }
}
