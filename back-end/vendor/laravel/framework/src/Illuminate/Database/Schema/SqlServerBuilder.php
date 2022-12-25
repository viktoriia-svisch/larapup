<?php
namespace Illuminate\Database\Schema;
class SqlServerBuilder extends Builder
{
    public function dropAllTables()
    {
        $this->disableForeignKeyConstraints();
        $this->connection->statement($this->grammar->compileDropAllTables());
        $this->enableForeignKeyConstraints();
    }
}
