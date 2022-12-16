<?php
namespace Illuminate\Validation;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Database\ConnectionResolverInterface;
class DatabasePresenceVerifier implements PresenceVerifierInterface
{
    protected $db;
    protected $connection;
    public function __construct(ConnectionResolverInterface $db)
    {
        $this->db = $db;
    }
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = [])
    {
        $query = $this->table($collection)->where($column, '=', $value);
        if (! is_null($excludeId) && $excludeId !== 'NULL') {
            $query->where($idColumn ?: 'id', '<>', $excludeId);
        }
        return $this->addConditions($query, $extra)->count();
    }
    public function getMultiCount($collection, $column, array $values, array $extra = [])
    {
        $query = $this->table($collection)->whereIn($column, $values);
        return $this->addConditions($query, $extra)->distinct()->count($column);
    }
    protected function addConditions($query, $conditions)
    {
        foreach ($conditions as $key => $value) {
            if ($value instanceof Closure) {
                $query->where(function ($query) use ($value) {
                    $value($query);
                });
            } else {
                $this->addWhere($query, $key, $value);
            }
        }
        return $query;
    }
    protected function addWhere($query, $key, $extraValue)
    {
        if ($extraValue === 'NULL') {
            $query->whereNull($key);
        } elseif ($extraValue === 'NOT_NULL') {
            $query->whereNotNull($key);
        } elseif (Str::startsWith($extraValue, '!')) {
            $query->where($key, '!=', mb_substr($extraValue, 1));
        } else {
            $query->where($key, $extraValue);
        }
    }
    protected function table($table)
    {
        return $this->db->connection($this->connection)->table($table)->useWritePdo();
    }
    public function setConnection($connection)
    {
        $this->connection = $connection;
    }
}
