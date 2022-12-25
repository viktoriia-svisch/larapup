<?php
namespace Illuminate\Database;
use Closure;
use Exception;
use Throwable;
use Illuminate\Database\Schema\SqlServerBuilder;
use Doctrine\DBAL\Driver\PDOSqlsrv\Driver as DoctrineDriver;
use Illuminate\Database\Query\Processors\SqlServerProcessor;
use Illuminate\Database\Query\Grammars\SqlServerGrammar as QueryGrammar;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar as SchemaGrammar;
class SqlServerConnection extends Connection
{
    public function transaction(Closure $callback, $attempts = 1)
    {
        for ($a = 1; $a <= $attempts; $a++) {
            if ($this->getDriverName() === 'sqlsrv') {
                return parent::transaction($callback);
            }
            $this->getPdo()->exec('BEGIN TRAN');
            try {
                $result = $callback($this);
                $this->getPdo()->exec('COMMIT TRAN');
            }
            catch (Exception $e) {
                $this->getPdo()->exec('ROLLBACK TRAN');
                throw $e;
            } catch (Throwable $e) {
                $this->getPdo()->exec('ROLLBACK TRAN');
                throw $e;
            }
            return $result;
        }
    }
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new QueryGrammar);
    }
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new SqlServerBuilder($this);
    }
    protected function getDefaultSchemaGrammar()
    {
        return $this->withTablePrefix(new SchemaGrammar);
    }
    protected function getDefaultPostProcessor()
    {
        return new SqlServerProcessor;
    }
    protected function getDoctrineDriver()
    {
        return new DoctrineDriver;
    }
}
