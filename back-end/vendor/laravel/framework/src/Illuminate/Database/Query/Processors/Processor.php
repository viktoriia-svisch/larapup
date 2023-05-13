<?php
namespace Illuminate\Database\Query\Processors;
use Illuminate\Database\Query\Builder;
class Processor
{
    public function processSelect(Builder $query, $results)
    {
        return $results;
    }
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);
        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);
        return is_numeric($id) ? (int) $id : $id;
    }
    public function processColumnListing($results)
    {
        return $results;
    }
}
