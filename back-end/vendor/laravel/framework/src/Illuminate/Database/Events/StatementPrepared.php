<?php
namespace Illuminate\Database\Events;
class StatementPrepared
{
    public $connection;
    public $statement;
    public function __construct($connection, $statement)
    {
        $this->statement = $statement;
        $this->connection = $connection;
    }
}
