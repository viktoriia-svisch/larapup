<?php
namespace Illuminate\Database;
use PDO;
use Closure;
use Exception;
use PDOStatement;
use LogicException;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Database\Query\Expression;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\QueryExecuted;
use Doctrine\DBAL\Connection as DoctrineConnection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Database\Query\Grammars\Grammar as QueryGrammar;
class Connection implements ConnectionInterface
{
    use DetectsDeadlocks,
        DetectsLostConnections,
        Concerns\ManagesTransactions;
    protected $pdo;
    protected $readPdo;
    protected $database;
    protected $tablePrefix = '';
    protected $config = [];
    protected $reconnector;
    protected $queryGrammar;
    protected $schemaGrammar;
    protected $postProcessor;
    protected $events;
    protected $fetchMode = PDO::FETCH_OBJ;
    protected $transactions = 0;
    protected $recordsModified = false;
    protected $queryLog = [];
    protected $loggingQueries = false;
    protected $pretending = false;
    protected $doctrineConnection;
    protected static $resolvers = [];
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;
        $this->database = $database;
        $this->tablePrefix = $tablePrefix;
        $this->config = $config;
        $this->useDefaultQueryGrammar();
        $this->useDefaultPostProcessor();
    }
    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }
    protected function getDefaultQueryGrammar()
    {
        return new QueryGrammar;
    }
    public function useDefaultSchemaGrammar()
    {
        $this->schemaGrammar = $this->getDefaultSchemaGrammar();
    }
    protected function getDefaultSchemaGrammar()
    {
    }
    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }
    protected function getDefaultPostProcessor()
    {
        return new Processor;
    }
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }
        return new SchemaBuilder($this);
    }
    public function table($table)
    {
        return $this->query()->from($table);
    }
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $records = $this->select($query, $bindings, $useReadPdo);
        return array_shift($records);
    }
    public function selectFromWriteConnection($query, $bindings = [])
    {
        return $this->select($query, $bindings, false);
    }
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                              ->prepare($query));
            $this->bindValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            return $statement->fetchAll();
        });
    }
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }
            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                              ->prepare($query));
            $this->bindValues(
                $statement, $this->prepareBindings($bindings)
            );
            $statement->execute();
            return $statement;
        });
        while ($record = $statement->fetch()) {
            yield $record;
        }
    }
    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);
        $this->event(new Events\StatementPrepared(
            $this, $statement
        ));
        return $statement;
    }
    protected function getPdoForSelect($useReadPdo = true)
    {
        return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
    }
    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }
    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }
    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }
    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }
            $statement = $this->getPdo()->prepare($query);
            $this->bindValues($statement, $this->prepareBindings($bindings));
            $this->recordsHaveBeenModified();
            return $statement->execute();
        });
    }
    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }
            $statement = $this->getPdo()->prepare($query);
            $this->bindValues($statement, $this->prepareBindings($bindings));
            $statement->execute();
            $this->recordsHaveBeenModified(
                ($count = $statement->rowCount()) > 0
            );
            return $count;
        });
    }
    public function unprepared($query)
    {
        return $this->run($query, [], function ($query) {
            if ($this->pretending()) {
                return true;
            }
            $this->recordsHaveBeenModified(
                $change = $this->getPdo()->exec($query) !== false
            );
            return $change;
        });
    }
    public function pretend(Closure $callback)
    {
        return $this->withFreshQueryLog(function () use ($callback) {
            $this->pretending = true;
            $callback($this);
            $this->pretending = false;
            return $this->queryLog;
        });
    }
    protected function withFreshQueryLog($callback)
    {
        $loggingQueries = $this->loggingQueries;
        $this->enableQueryLog();
        $this->queryLog = [];
        $result = $callback();
        $this->loggingQueries = $loggingQueries;
        return $result;
    }
    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();
        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }
        return $bindings;
    }
    protected function run($query, $bindings, Closure $callback)
    {
        $this->reconnectIfMissingConnection();
        $start = microtime(true);
        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (QueryException $e) {
            $result = $this->handleQueryException(
                $e, $query, $bindings, $callback
            );
        }
        $this->logQuery(
            $query, $bindings, $this->getElapsedTime($start)
        );
        return $result;
    }
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        try {
            $result = $callback($query, $bindings);
        }
        catch (Exception $e) {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }
        return $result;
    }
    public function logQuery($query, $bindings, $time = null)
    {
        $this->event(new QueryExecuted($query, $bindings, $time, $this));
        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }
    protected function handleQueryException($e, $query, $bindings, Closure $callback)
    {
        if ($this->transactions >= 1) {
            throw $e;
        }
        return $this->tryAgainIfCausedByLostConnection(
            $e, $query, $bindings, $callback
        );
    }
    protected function tryAgainIfCausedByLostConnection(QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->causedByLostConnection($e->getPrevious())) {
            $this->reconnect();
            return $this->runQueryCallback($query, $bindings, $callback);
        }
        throw $e;
    }
    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            $this->doctrineConnection = null;
            return call_user_func($this->reconnector, $this);
        }
        throw new LogicException('Lost connection and no reconnector available.');
    }
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->pdo)) {
            $this->reconnect();
        }
    }
    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);
    }
    public function listen(Closure $callback)
    {
        if (isset($this->events)) {
            $this->events->listen(Events\QueryExecuted::class, $callback);
        }
    }
    protected function fireConnectionEvent($event)
    {
        if (! isset($this->events)) {
            return;
        }
        switch ($event) {
            case 'beganTransaction':
                return $this->events->dispatch(new Events\TransactionBeginning($this));
            case 'committed':
                return $this->events->dispatch(new Events\TransactionCommitted($this));
            case 'rollingBack':
                return $this->events->dispatch(new Events\TransactionRolledBack($this));
        }
    }
    protected function event($event)
    {
        if (isset($this->events)) {
            $this->events->dispatch($event);
        }
    }
    public function raw($value)
    {
        return new Expression($value);
    }
    public function recordsHaveBeenModified($value = true)
    {
        if (! $this->recordsModified) {
            $this->recordsModified = $value;
        }
    }
    public function isDoctrineAvailable()
    {
        return class_exists('Doctrine\DBAL\Connection');
    }
    public function getDoctrineColumn($table, $column)
    {
        $schema = $this->getDoctrineSchemaManager();
        return $schema->listTableDetails($table)->getColumn($column);
    }
    public function getDoctrineSchemaManager()
    {
        return $this->getDoctrineDriver()->getSchemaManager($this->getDoctrineConnection());
    }
    public function getDoctrineConnection()
    {
        if (is_null($this->doctrineConnection)) {
            $driver = $this->getDoctrineDriver();
            $this->doctrineConnection = new DoctrineConnection([
                'pdo' => $this->getPdo(),
                'dbname' => $this->getConfig('database'),
                'driver' => $driver->getName(),
            ], $driver);
        }
        return $this->doctrineConnection;
    }
    public function getPdo()
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }
        return $this->pdo;
    }
    public function getReadPdo()
    {
        if ($this->transactions > 0) {
            return $this->getPdo();
        }
        if ($this->recordsModified && $this->getConfig('sticky')) {
            return $this->getPdo();
        }
        if ($this->readPdo instanceof Closure) {
            return $this->readPdo = call_user_func($this->readPdo);
        }
        return $this->readPdo ?: $this->getPdo();
    }
    public function setPdo($pdo)
    {
        $this->transactions = 0;
        $this->pdo = $pdo;
        return $this;
    }
    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;
        return $this;
    }
    public function setReconnector(callable $reconnector)
    {
        $this->reconnector = $reconnector;
        return $this;
    }
    public function getName()
    {
        return $this->getConfig('name');
    }
    public function getConfig($option = null)
    {
        return Arr::get($this->config, $option);
    }
    public function getDriverName()
    {
        return $this->getConfig('driver');
    }
    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }
    public function setQueryGrammar(Query\Grammars\Grammar $grammar)
    {
        $this->queryGrammar = $grammar;
        return $this;
    }
    public function getSchemaGrammar()
    {
        return $this->schemaGrammar;
    }
    public function setSchemaGrammar(Schema\Grammars\Grammar $grammar)
    {
        $this->schemaGrammar = $grammar;
        return $this;
    }
    public function getPostProcessor()
    {
        return $this->postProcessor;
    }
    public function setPostProcessor(Processor $processor)
    {
        $this->postProcessor = $processor;
        return $this;
    }
    public function getEventDispatcher()
    {
        return $this->events;
    }
    public function setEventDispatcher(Dispatcher $events)
    {
        $this->events = $events;
        return $this;
    }
    public function unsetEventDispatcher()
    {
        $this->events = null;
    }
    public function pretending()
    {
        return $this->pretending === true;
    }
    public function getQueryLog()
    {
        return $this->queryLog;
    }
    public function flushQueryLog()
    {
        $this->queryLog = [];
    }
    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }
    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }
    public function logging()
    {
        return $this->loggingQueries;
    }
    public function getDatabaseName()
    {
        return $this->database;
    }
    public function setDatabaseName($database)
    {
        $this->database = $database;
        return $this;
    }
    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }
    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;
        $this->getQueryGrammar()->setTablePrefix($prefix);
        return $this;
    }
    public function withTablePrefix(Grammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);
        return $grammar;
    }
    public static function resolverFor($driver, Closure $callback)
    {
        static::$resolvers[$driver] = $callback;
    }
    public static function getResolver($driver)
    {
        return static::$resolvers[$driver] ?? null;
    }
}
