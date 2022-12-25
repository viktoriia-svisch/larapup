<?php
namespace Illuminate\Database\Query\Processors;
use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
class SqlServerProcessor extends Processor
{
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $connection = $query->getConnection();
        $connection->insert($sql, $values);
        if ($connection->getConfig('odbc') === true) {
            $id = $this->processInsertGetIdForOdbc($connection);
        } else {
            $id = $connection->getPdo()->lastInsertId();
        }
        return is_numeric($id) ? (int) $id : $id;
    }
    protected function processInsertGetIdForOdbc(Connection $connection)
    {
        $result = $connection->selectFromWriteConnection(
            'SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS int) AS insertid'
        );
        if (! $result) {
            throw new Exception('Unable to retrieve lastInsertID for ODBC.');
        }
        $row = $result[0];
        return is_object($row) ? $row->insertid : $row['insertid'];
    }
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object) $result)->name;
        }, $results);
    }
}
