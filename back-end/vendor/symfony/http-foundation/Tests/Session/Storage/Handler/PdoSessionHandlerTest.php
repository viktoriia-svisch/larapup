<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
class PdoSessionHandlerTest extends TestCase
{
    private $dbFile;
    protected function tearDown()
    {
        if ($this->dbFile) {
            @unlink($this->dbFile);
        }
        parent::tearDown();
    }
    protected function getPersistentSqliteDsn()
    {
        $this->dbFile = tempnam(sys_get_temp_dir(), 'sf_sqlite_sessions');
        return 'sqlite:'.$this->dbFile;
    }
    protected function getMemorySqlitePdo()
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $storage = new PdoSessionHandler($pdo);
        $storage->createTable();
        return $pdo;
    }
    public function testWrongPdoErrMode()
    {
        $pdo = $this->getMemorySqlitePdo();
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $storage = new PdoSessionHandler($pdo);
    }
    public function testInexistentTable()
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo(), ['db_table' => 'inexistent_table']);
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();
    }
    public function testCreateTableTwice()
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo());
        $storage->createTable();
    }
    public function testWithLazyDsnConnection()
    {
        $dsn = $this->getPersistentSqliteDsn();
        $storage = new PdoSessionHandler($dsn);
        $storage->createTable();
        $storage->open('', 'sid');
        $data = $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();
        $this->assertSame('', $data, 'New session returns empty string data');
        $storage->open('', 'sid');
        $data = $storage->read('id');
        $storage->close();
        $this->assertSame('data', $data, 'Written value can be read back correctly');
    }
    public function testWithLazySavePathConnection()
    {
        $dsn = $this->getPersistentSqliteDsn();
        $storage = new PdoSessionHandler(null);
        $storage->open($dsn, 'sid');
        $storage->createTable();
        $data = $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();
        $this->assertSame('', $data, 'New session returns empty string data');
        $storage->open($dsn, 'sid');
        $data = $storage->read('id');
        $storage->close();
        $this->assertSame('data', $data, 'Written value can be read back correctly');
    }
    public function testReadWriteReadWithNullByte()
    {
        $sessionData = 'da'."\0".'ta';
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo());
        $storage->open('', 'sid');
        $readData = $storage->read('id');
        $storage->write('id', $sessionData);
        $storage->close();
        $this->assertSame('', $readData, 'New session returns empty string data');
        $storage->open('', 'sid');
        $readData = $storage->read('id');
        $storage->close();
        $this->assertSame($sessionData, $readData, 'Written value can be read back correctly');
    }
    public function testReadConvertsStreamToString()
    {
        $pdo = new MockPdo('pgsql');
        $pdo->prepareResult = $this->getMockBuilder('PDOStatement')->getMock();
        $content = 'foobar';
        $stream = $this->createStream($content);
        $pdo->prepareResult->expects($this->once())->method('fetchAll')
            ->will($this->returnValue([[$stream, 42, time()]]));
        $storage = new PdoSessionHandler($pdo);
        $result = $storage->read('foo');
        $this->assertSame($content, $result);
    }
    public function testReadLockedConvertsStreamToString()
    {
        if (filter_var(ini_get('session.use_strict_mode'), FILTER_VALIDATE_BOOLEAN)) {
            $this->markTestSkipped('Strict mode needs no locking for new sessions.');
        }
        $pdo = new MockPdo('pgsql');
        $selectStmt = $this->getMockBuilder('PDOStatement')->getMock();
        $insertStmt = $this->getMockBuilder('PDOStatement')->getMock();
        $pdo->prepareResult = function ($statement) use ($selectStmt, $insertStmt) {
            return 0 === strpos($statement, 'INSERT') ? $insertStmt : $selectStmt;
        };
        $content = 'foobar';
        $stream = $this->createStream($content);
        $exception = null;
        $selectStmt->expects($this->atLeast(2))->method('fetchAll')
            ->will($this->returnCallback(function () use (&$exception, $stream) {
                return $exception ? [[$stream, 42, time()]] : [];
            }));
        $insertStmt->expects($this->once())->method('execute')
            ->will($this->returnCallback(function () use (&$exception) {
                throw $exception = new \PDOException('', '23');
            }));
        $storage = new PdoSessionHandler($pdo);
        $result = $storage->read('foo');
        $this->assertSame($content, $result);
    }
    public function testReadingRequiresExactlySameId()
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo());
        $storage->open('', 'sid');
        $storage->write('id', 'data');
        $storage->write('test', 'data');
        $storage->write('space ', 'data');
        $storage->close();
        $storage->open('', 'sid');
        $readDataCaseSensitive = $storage->read('ID');
        $readDataNoCharFolding = $storage->read('tést');
        $readDataKeepSpace = $storage->read('space ');
        $readDataExtraSpace = $storage->read('space  ');
        $storage->close();
        $this->assertSame('', $readDataCaseSensitive, 'Retrieval by ID should be case-sensitive (collation setting)');
        $this->assertSame('', $readDataNoCharFolding, 'Retrieval by ID should not do character folding (collation setting)');
        $this->assertSame('data', $readDataKeepSpace, 'Retrieval by ID requires spaces as-is');
        $this->assertSame('', $readDataExtraSpace, 'Retrieval by ID requires spaces as-is');
    }
    public function testWriteDifferentSessionIdThanRead()
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo());
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->destroy('id');
        $storage->write('new_id', 'data_of_new_session_id');
        $storage->close();
        $storage->open('', 'sid');
        $data = $storage->read('new_id');
        $storage->close();
        $this->assertSame('data_of_new_session_id', $data, 'Data of regenerated session id is available');
    }
    public function testWrongUsageStillWorks()
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo());
        $storage->write('id', 'data');
        $storage->write('other_id', 'other_data');
        $storage->destroy('inexistent');
        $storage->open('', 'sid');
        $data = $storage->read('id');
        $otherData = $storage->read('other_id');
        $storage->close();
        $this->assertSame('data', $data);
        $this->assertSame('other_data', $otherData);
    }
    public function testSessionDestroy()
    {
        $pdo = $this->getMemorySqlitePdo();
        $storage = new PdoSessionHandler($pdo);
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();
        $this->assertEquals(1, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn());
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->destroy('id');
        $storage->close();
        $this->assertEquals(0, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn());
        $storage->open('', 'sid');
        $data = $storage->read('id');
        $storage->close();
        $this->assertSame('', $data, 'Destroyed session returns empty string');
    }
    public function testSessionGC()
    {
        $previousLifeTime = ini_set('session.gc_maxlifetime', 1000);
        $pdo = $this->getMemorySqlitePdo();
        $storage = new PdoSessionHandler($pdo);
        $storage->open('', 'sid');
        $storage->read('id');
        $storage->write('id', 'data');
        $storage->close();
        $storage->open('', 'sid');
        $storage->read('gc_id');
        ini_set('session.gc_maxlifetime', -1); 
        $storage->write('gc_id', 'data');
        $storage->close();
        $this->assertEquals(2, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn(), 'No session pruned because gc not called');
        $storage->open('', 'sid');
        $data = $storage->read('gc_id');
        $storage->gc(-1);
        $storage->close();
        ini_set('session.gc_maxlifetime', $previousLifeTime);
        $this->assertSame('', $data, 'Session already considered garbage, so not returning data even if it is not pruned yet');
        $this->assertEquals(1, $pdo->query('SELECT COUNT(*) FROM sessions')->fetchColumn(), 'Expired session is pruned');
    }
    public function testGetConnection()
    {
        $storage = new PdoSessionHandler($this->getMemorySqlitePdo());
        $method = new \ReflectionMethod($storage, 'getConnection');
        $method->setAccessible(true);
        $this->assertInstanceOf('\PDO', $method->invoke($storage));
    }
    public function testGetConnectionConnectsIfNeeded()
    {
        $storage = new PdoSessionHandler('sqlite::memory:');
        $method = new \ReflectionMethod($storage, 'getConnection');
        $method->setAccessible(true);
        $this->assertInstanceOf('\PDO', $method->invoke($storage));
    }
    public function testUrlDsn($url, $expectedDsn, $expectedUser = null, $expectedPassword = null)
    {
        $storage = new PdoSessionHandler($url);
        $this->assertAttributeEquals($expectedDsn, 'dsn', $storage);
        if (null !== $expectedUser) {
            $this->assertAttributeEquals($expectedUser, 'username', $storage);
        }
        if (null !== $expectedPassword) {
            $this->assertAttributeEquals($expectedPassword, 'password', $storage);
        }
    }
    public function provideUrlDsnPairs()
    {
        yield ['mysql:
        yield ['mysql:
        yield ['mysql2:
        yield ['postgres:
        yield ['postgresql:
        yield ['postgres:
        yield 'sqlite relative path' => ['sqlite:
        yield 'sqlite absolute path' => ['sqlite:
        yield 'sqlite relative path without host' => ['sqlite:
        yield 'sqlite absolute path without host' => ['sqlite3:
        yield ['sqlite:
        yield ['mssql:
        yield ['mssql:
    }
    private function createStream($content)
    {
        $stream = tmpfile();
        fwrite($stream, $content);
        fseek($stream, 0);
        return $stream;
    }
}
class MockPdo extends \PDO
{
    public $prepareResult;
    private $driverName;
    private $errorMode;
    public function __construct($driverName = null, $errorMode = null)
    {
        $this->driverName = $driverName;
        $this->errorMode = null !== $errorMode ?: \PDO::ERRMODE_EXCEPTION;
    }
    public function getAttribute($attribute)
    {
        if (\PDO::ATTR_ERRMODE === $attribute) {
            return $this->errorMode;
        }
        if (\PDO::ATTR_DRIVER_NAME === $attribute) {
            return $this->driverName;
        }
        return parent::getAttribute($attribute);
    }
    public function prepare($statement, $driverOptions = [])
    {
        return \is_callable($this->prepareResult)
            ? ($this->prepareResult)($statement, $driverOptions)
            : $this->prepareResult;
    }
    public function beginTransaction()
    {
    }
    public function rollBack()
    {
    }
}