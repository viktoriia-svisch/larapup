<?php
namespace Illuminate\Contracts\Database;
class ModelIdentifier
{
    public $class;
    public $id;
    public $relations;
    public $connection;
    public function __construct($class, $id, array $relations, $connection)
    {
        $this->id = $id;
        $this->class = $class;
        $this->relations = $relations;
        $this->connection = $connection;
    }
}
