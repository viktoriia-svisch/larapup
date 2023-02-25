<?php
namespace Illuminate\Database\Query\Processors;
class MySqlProcessor extends Processor
{
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
    }
}
