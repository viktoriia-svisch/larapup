<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class PdoSessionHandler extends AbstractSessionHandler
{
    const LOCK_NONE = 0;
    const LOCK_ADVISORY = 1;
    const LOCK_TRANSACTIONAL = 2;
    private $pdo;
    private $dsn = false;
    private $driver;
    private $table = 'sessions';
    private $idCol = 'sess_id';
    private $dataCol = 'sess_data';
    private $lifetimeCol = 'sess_lifetime';
    private $timeCol = 'sess_time';
    private $username = '';
    private $password = '';
    private $connectionOptions = [];
    private $lockMode = self::LOCK_TRANSACTIONAL;
    private $unlockStatements = [];
    private $sessionExpired = false;
    private $inTransaction = false;
    private $gcCalled = false;
    public function __construct($pdoOrDsn = null, array $options = [])
    {
        if ($pdoOrDsn instanceof \PDO) {
            if (\PDO::ERRMODE_EXCEPTION !== $pdoOrDsn->getAttribute(\PDO::ATTR_ERRMODE)) {
                throw new \InvalidArgumentException(sprintf('"%s" requires PDO error mode attribute be set to throw Exceptions (i.e. $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION))', __CLASS__));
            }
            $this->pdo = $pdoOrDsn;
            $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } elseif (\is_string($pdoOrDsn) && false !== strpos($pdoOrDsn, ':
            $this->dsn = $this->buildDsnFromUrl($pdoOrDsn);
        } else {
            $this->dsn = $pdoOrDsn;
        }
        $this->table = isset($options['db_table']) ? $options['db_table'] : $this->table;
        $this->idCol = isset($options['db_id_col']) ? $options['db_id_col'] : $this->idCol;
        $this->dataCol = isset($options['db_data_col']) ? $options['db_data_col'] : $this->dataCol;
        $this->lifetimeCol = isset($options['db_lifetime_col']) ? $options['db_lifetime_col'] : $this->lifetimeCol;
        $this->timeCol = isset($options['db_time_col']) ? $options['db_time_col'] : $this->timeCol;
        $this->username = isset($options['db_username']) ? $options['db_username'] : $this->username;
        $this->password = isset($options['db_password']) ? $options['db_password'] : $this->password;
        $this->connectionOptions = isset($options['db_connection_options']) ? $options['db_connection_options'] : $this->connectionOptions;
        $this->lockMode = isset($options['lock_mode']) ? $options['lock_mode'] : $this->lockMode;
    }
    public function createTable()
    {
        $this->getConnection();
        switch ($this->driver) {
            case 'mysql':
                $sql = "CREATE TABLE $this->table ($this->idCol VARBINARY(128) NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol MEDIUMINT NOT NULL, $this->timeCol INTEGER UNSIGNED NOT NULL) COLLATE utf8_bin, ENGINE = InnoDB";
                break;
            case 'sqlite':
                $sql = "CREATE TABLE $this->table ($this->idCol TEXT NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            case 'pgsql':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->dataCol BYTEA NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            case 'oci':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR2(128) NOT NULL PRIMARY KEY, $this->dataCol BLOB NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            case 'sqlsrv':
                $sql = "CREATE TABLE $this->table ($this->idCol VARCHAR(128) NOT NULL PRIMARY KEY, $this->dataCol VARBINARY(MAX) NOT NULL, $this->lifetimeCol INTEGER NOT NULL, $this->timeCol INTEGER NOT NULL)";
                break;
            default:
                throw new \DomainException(sprintf('Creating the session table is currently not implemented for PDO driver "%s".', $this->driver));
        }
        try {
            $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
    }
    public function isSessionExpired()
    {
        return $this->sessionExpired;
    }
    public function open($savePath, $sessionName)
    {
        $this->sessionExpired = false;
        if (null === $this->pdo) {
            $this->connect($this->dsn ?: $savePath);
        }
        return parent::open($savePath, $sessionName);
    }
    public function read($sessionId)
    {
        try {
            return parent::read($sessionId);
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
    }
    public function gc($maxlifetime)
    {
        $this->gcCalled = true;
        return true;
    }
    protected function doDestroy($sessionId)
    {
        $sql = "DELETE FROM $this->table WHERE $this->idCol = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $stmt->execute();
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
        return true;
    }
    protected function doWrite($sessionId, $data)
    {
        $maxlifetime = (int) ini_get('session.gc_maxlifetime');
        try {
            $mergeStmt = $this->getMergeStatement($sessionId, $data, $maxlifetime);
            if (null !== $mergeStmt) {
                $mergeStmt->execute();
                return true;
            }
            $updateStmt = $this->getUpdateStatement($sessionId, $data, $maxlifetime);
            $updateStmt->execute();
            if (!$updateStmt->rowCount()) {
                try {
                    $insertStmt = $this->getInsertStatement($sessionId, $data, $maxlifetime);
                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    if (0 === strpos($e->getCode(), '23')) {
                        $updateStmt->execute();
                    } else {
                        throw $e;
                    }
                }
            }
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
        return true;
    }
    public function updateTimestamp($sessionId, $data)
    {
        $maxlifetime = (int) ini_get('session.gc_maxlifetime');
        try {
            $updateStmt = $this->pdo->prepare(
                "UPDATE $this->table SET $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id"
            );
            $updateStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $updateStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
            $updateStmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $updateStmt->execute();
        } catch (\PDOException $e) {
            $this->rollback();
            throw $e;
        }
        return true;
    }
    public function close()
    {
        $this->commit();
        while ($unlockStmt = array_shift($this->unlockStatements)) {
            $unlockStmt->execute();
        }
        if ($this->gcCalled) {
            $this->gcCalled = false;
            if ('mysql' === $this->driver) {
                $sql = "DELETE FROM $this->table WHERE $this->lifetimeCol + $this->timeCol < :time";
            } else {
                $sql = "DELETE FROM $this->table WHERE $this->lifetimeCol < :time - $this->timeCol";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
            $stmt->execute();
        }
        if (false !== $this->dsn) {
            $this->pdo = null; 
        }
        return true;
    }
    private function connect($dsn)
    {
        $this->pdo = new \PDO($dsn, $this->username, $this->password, $this->connectionOptions);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->driver = $this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    }
    private function buildDsnFromUrl($dsnOrUrl)
    {
        $url = preg_replace('#^((?:pdo_)?sqlite3?):
        $params = parse_url($url);
        if (false === $params) {
            return $dsnOrUrl; 
        }
        $params = array_map('rawurldecode', $params);
        if (isset($params['user'])) {
            $this->username = $params['user'];
        }
        if (isset($params['pass'])) {
            $this->password = $params['pass'];
        }
        if (!isset($params['scheme'])) {
            throw new \InvalidArgumentException('URLs without scheme are not supported to configure the PdoSessionHandler');
        }
        $driverAliasMap = [
            'mssql' => 'sqlsrv',
            'mysql2' => 'mysql', 
            'postgres' => 'pgsql',
            'postgresql' => 'pgsql',
            'sqlite3' => 'sqlite',
        ];
        $driver = isset($driverAliasMap[$params['scheme']]) ? $driverAliasMap[$params['scheme']] : $params['scheme'];
        if (0 === strpos($driver, 'pdo_') || 0 === strpos($driver, 'pdo-')) {
            $driver = substr($driver, 4);
        }
        switch ($driver) {
            case 'mysql':
            case 'pgsql':
                $dsn = $driver.':';
                if (isset($params['host']) && '' !== $params['host']) {
                    $dsn .= 'host='.$params['host'].';';
                }
                if (isset($params['port']) && '' !== $params['port']) {
                    $dsn .= 'port='.$params['port'].';';
                }
                if (isset($params['path'])) {
                    $dbName = substr($params['path'], 1); 
                    $dsn .= 'dbname='.$dbName.';';
                }
                return $dsn;
            case 'sqlite':
                return 'sqlite:'.substr($params['path'], 1);
            case 'sqlsrv':
                $dsn = 'sqlsrv:server=';
                if (isset($params['host'])) {
                    $dsn .= $params['host'];
                }
                if (isset($params['port']) && '' !== $params['port']) {
                    $dsn .= ','.$params['port'];
                }
                if (isset($params['path'])) {
                    $dbName = substr($params['path'], 1); 
                    $dsn .= ';Database='.$dbName;
                }
                return $dsn;
            default:
                throw new \InvalidArgumentException(sprintf('The scheme "%s" is not supported by the PdoSessionHandler URL configuration. Pass a PDO DSN directly.', $params['scheme']));
        }
    }
    private function beginTransaction()
    {
        if (!$this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('BEGIN IMMEDIATE TRANSACTION');
            } else {
                if ('mysql' === $this->driver) {
                    $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
                }
                $this->pdo->beginTransaction();
            }
            $this->inTransaction = true;
        }
    }
    private function commit()
    {
        if ($this->inTransaction) {
            try {
                if ('sqlite' === $this->driver) {
                    $this->pdo->exec('COMMIT');
                } else {
                    $this->pdo->commit();
                }
                $this->inTransaction = false;
            } catch (\PDOException $e) {
                $this->rollback();
                throw $e;
            }
        }
    }
    private function rollback()
    {
        if ($this->inTransaction) {
            if ('sqlite' === $this->driver) {
                $this->pdo->exec('ROLLBACK');
            } else {
                $this->pdo->rollBack();
            }
            $this->inTransaction = false;
        }
    }
    protected function doRead($sessionId)
    {
        if (self::LOCK_ADVISORY === $this->lockMode) {
            $this->unlockStatements[] = $this->doAdvisoryLock($sessionId);
        }
        $selectSql = $this->getSelectSql();
        $selectStmt = $this->pdo->prepare($selectSql);
        $selectStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $insertStmt = null;
        do {
            $selectStmt->execute();
            $sessionRows = $selectStmt->fetchAll(\PDO::FETCH_NUM);
            if ($sessionRows) {
                if ($sessionRows[0][1] + $sessionRows[0][2] < time()) {
                    $this->sessionExpired = true;
                    return '';
                }
                return \is_resource($sessionRows[0][0]) ? stream_get_contents($sessionRows[0][0]) : $sessionRows[0][0];
            }
            if (null !== $insertStmt) {
                $this->rollback();
                throw new \RuntimeException('Failed to read session: INSERT reported a duplicate id but next SELECT did not return any data.');
            }
            if (!filter_var(ini_get('session.use_strict_mode'), FILTER_VALIDATE_BOOLEAN) && self::LOCK_TRANSACTIONAL === $this->lockMode && 'sqlite' !== $this->driver) {
                try {
                    $insertStmt = $this->getInsertStatement($sessionId, '', 0);
                    $insertStmt->execute();
                } catch (\PDOException $e) {
                    if (0 === strpos($e->getCode(), '23')) {
                        $this->rollback();
                        $this->beginTransaction();
                        continue;
                    }
                    throw $e;
                }
            }
            return '';
        } while (true);
    }
    private function doAdvisoryLock(string $sessionId)
    {
        switch ($this->driver) {
            case 'mysql':
                $lockId = \substr($sessionId, 0, 64);
                $stmt = $this->pdo->prepare('SELECT GET_LOCK(:key, 50)');
                $stmt->bindValue(':key', $lockId, \PDO::PARAM_STR);
                $stmt->execute();
                $releaseStmt = $this->pdo->prepare('DO RELEASE_LOCK(:key)');
                $releaseStmt->bindValue(':key', $lockId, \PDO::PARAM_STR);
                return $releaseStmt;
            case 'pgsql':
                if (4 === \PHP_INT_SIZE) {
                    $sessionInt1 = $this->convertStringToInt($sessionId);
                    $sessionInt2 = $this->convertStringToInt(substr($sessionId, 4, 4));
                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key1, :key2)');
                    $stmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $stmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                    $stmt->execute();
                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key1, :key2)');
                    $releaseStmt->bindValue(':key1', $sessionInt1, \PDO::PARAM_INT);
                    $releaseStmt->bindValue(':key2', $sessionInt2, \PDO::PARAM_INT);
                } else {
                    $sessionBigInt = $this->convertStringToInt($sessionId);
                    $stmt = $this->pdo->prepare('SELECT pg_advisory_lock(:key)');
                    $stmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                    $stmt->execute();
                    $releaseStmt = $this->pdo->prepare('SELECT pg_advisory_unlock(:key)');
                    $releaseStmt->bindValue(':key', $sessionBigInt, \PDO::PARAM_INT);
                }
                return $releaseStmt;
            case 'sqlite':
                throw new \DomainException('SQLite does not support advisory locks.');
            default:
                throw new \DomainException(sprintf('Advisory locks are currently not implemented for PDO driver "%s".', $this->driver));
        }
    }
    private function convertStringToInt(string $string): int
    {
        if (4 === \PHP_INT_SIZE) {
            return (\ord($string[3]) << 24) + (\ord($string[2]) << 16) + (\ord($string[1]) << 8) + \ord($string[0]);
        }
        $int1 = (\ord($string[7]) << 24) + (\ord($string[6]) << 16) + (\ord($string[5]) << 8) + \ord($string[4]);
        $int2 = (\ord($string[3]) << 24) + (\ord($string[2]) << 16) + (\ord($string[1]) << 8) + \ord($string[0]);
        return $int2 + ($int1 << 32);
    }
    private function getSelectSql(): string
    {
        if (self::LOCK_TRANSACTIONAL === $this->lockMode) {
            $this->beginTransaction();
            switch ($this->driver) {
                case 'mysql':
                case 'oci':
                case 'pgsql':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id FOR UPDATE";
                case 'sqlsrv':
                    return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WITH (UPDLOCK, ROWLOCK) WHERE $this->idCol = :id";
                case 'sqlite':
                    break;
                default:
                    throw new \DomainException(sprintf('Transactional locks are currently not implemented for PDO driver "%s".', $this->driver));
            }
        }
        return "SELECT $this->dataCol, $this->lifetimeCol, $this->timeCol FROM $this->table WHERE $this->idCol = :id";
    }
    private function getInsertStatement($sessionId, $sessionData, $maxlifetime)
    {
        switch ($this->driver) {
            case 'oci':
                $data = fopen('php:
                fwrite($data, $sessionData);
                rewind($data);
                $sql = "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, EMPTY_BLOB(), :lifetime, :time) RETURNING $this->dataCol into :data";
                break;
            default:
                $data = $sessionData;
                $sql = "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)";
                break;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, \PDO::PARAM_LOB);
        $stmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
        $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
        return $stmt;
    }
    private function getUpdateStatement($sessionId, $sessionData, $maxlifetime)
    {
        switch ($this->driver) {
            case 'oci':
                $data = fopen('php:
                fwrite($data, $sessionData);
                rewind($data);
                $sql = "UPDATE $this->table SET $this->dataCol = EMPTY_BLOB(), $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id RETURNING $this->dataCol into :data";
                break;
            default:
                $data = $sessionData;
                $sql = "UPDATE $this->table SET $this->dataCol = :data, $this->lifetimeCol = :lifetime, $this->timeCol = :time WHERE $this->idCol = :id";
                break;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
        $stmt->bindParam(':data', $data, \PDO::PARAM_LOB);
        $stmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
        $stmt->bindValue(':time', time(), \PDO::PARAM_INT);
        return $stmt;
    }
    private function getMergeStatement(string $sessionId, string $data, int $maxlifetime): ?\PDOStatement
    {
        switch (true) {
            case 'mysql' === $this->driver:
                $mergeSql = "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) ".
                    "ON DUPLICATE KEY UPDATE $this->dataCol = VALUES($this->dataCol), $this->lifetimeCol = VALUES($this->lifetimeCol), $this->timeCol = VALUES($this->timeCol)";
                break;
            case 'sqlsrv' === $this->driver && version_compare($this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '10', '>='):
                $mergeSql = "MERGE INTO $this->table WITH (HOLDLOCK) USING (SELECT 1 AS dummy) AS src ON ($this->idCol = ?) ".
                    "WHEN NOT MATCHED THEN INSERT ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (?, ?, ?, ?) ".
                    "WHEN MATCHED THEN UPDATE SET $this->dataCol = ?, $this->lifetimeCol = ?, $this->timeCol = ?;";
                break;
            case 'sqlite' === $this->driver:
                $mergeSql = "INSERT OR REPLACE INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time)";
                break;
            case 'pgsql' === $this->driver && version_compare($this->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '9.5', '>='):
                $mergeSql = "INSERT INTO $this->table ($this->idCol, $this->dataCol, $this->lifetimeCol, $this->timeCol) VALUES (:id, :data, :lifetime, :time) ".
                    "ON CONFLICT ($this->idCol) DO UPDATE SET ($this->dataCol, $this->lifetimeCol, $this->timeCol) = (EXCLUDED.$this->dataCol, EXCLUDED.$this->lifetimeCol, EXCLUDED.$this->timeCol)";
                break;
            default:
                return null;
        }
        $mergeStmt = $this->pdo->prepare($mergeSql);
        if ('sqlsrv' === $this->driver) {
            $mergeStmt->bindParam(1, $sessionId, \PDO::PARAM_STR);
            $mergeStmt->bindParam(2, $sessionId, \PDO::PARAM_STR);
            $mergeStmt->bindParam(3, $data, \PDO::PARAM_LOB);
            $mergeStmt->bindParam(4, $maxlifetime, \PDO::PARAM_INT);
            $mergeStmt->bindValue(5, time(), \PDO::PARAM_INT);
            $mergeStmt->bindParam(6, $data, \PDO::PARAM_LOB);
            $mergeStmt->bindParam(7, $maxlifetime, \PDO::PARAM_INT);
            $mergeStmt->bindValue(8, time(), \PDO::PARAM_INT);
        } else {
            $mergeStmt->bindParam(':id', $sessionId, \PDO::PARAM_STR);
            $mergeStmt->bindParam(':data', $data, \PDO::PARAM_LOB);
            $mergeStmt->bindParam(':lifetime', $maxlifetime, \PDO::PARAM_INT);
            $mergeStmt->bindValue(':time', time(), \PDO::PARAM_INT);
        }
        return $mergeStmt;
    }
    protected function getConnection()
    {
        if (null === $this->pdo) {
            $this->connect($this->dsn ?: ini_get('session.save_path'));
        }
        return $this->pdo;
    }
}
