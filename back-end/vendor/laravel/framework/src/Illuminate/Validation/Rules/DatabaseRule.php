<?php
namespace Illuminate\Validation\Rules;
use Closure;
trait DatabaseRule
{
    protected $table;
    protected $column;
    protected $wheres = [];
    protected $using = [];
    public function __construct($table, $column = 'NULL')
    {
        $this->table = $table;
        $this->column = $column;
    }
    public function where($column, $value = null)
    {
        if (is_array($value)) {
            return $this->whereIn($column, $value);
        }
        if ($column instanceof Closure) {
            return $this->using($column);
        }
        $this->wheres[] = compact('column', 'value');
        return $this;
    }
    public function whereNot($column, $value)
    {
        if (is_array($value)) {
            return $this->whereNotIn($column, $value);
        }
        return $this->where($column, '!'.$value);
    }
    public function whereNull($column)
    {
        return $this->where($column, 'NULL');
    }
    public function whereNotNull($column)
    {
        return $this->where($column, 'NOT_NULL');
    }
    public function whereIn($column, array $values)
    {
        return $this->where(function ($query) use ($column, $values) {
            $query->whereIn($column, $values);
        });
    }
    public function whereNotIn($column, array $values)
    {
        return $this->where(function ($query) use ($column, $values) {
            $query->whereNotIn($column, $values);
        });
    }
    public function using(Closure $callback)
    {
        $this->using[] = $callback;
        return $this;
    }
    public function queryCallbacks()
    {
        return $this->using;
    }
    protected function formatWheres()
    {
        return collect($this->wheres)->map(function ($where) {
            return $where['column'].','.$where['value'];
        })->implode(',');
    }
}
