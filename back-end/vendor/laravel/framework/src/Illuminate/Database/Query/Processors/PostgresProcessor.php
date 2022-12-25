<?php
namespace Illuminate\Database\Query\Processors;
use Illuminate\Database\Query\Builder;
class PostgresProcessor extends Processor
{
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $result = $query->getConnection()->selectFromWriteConnection($sql, $values)[0];
        $sequence = $sequence ?: 'id';
        $id = is_object($result) ? $result->{$sequence} : $result[$sequence];
        return is_numeric($id) ? (int) $id : $id;
    }
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
    }
}
