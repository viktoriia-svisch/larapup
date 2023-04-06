<?php
namespace Symfony\Component\VarDumper\Tests\Caster;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
class RedisCasterTest extends TestCase
{
    use VarDumperTestTrait;
    public function testNotConnected()
    {
        $redis = new \Redis();
        $xCast = <<<'EODUMP'
Redis {
  isConnected: false
}
EODUMP;
        $this->assertDumpMatchesFormat($xCast, $redis);
    }
    public function testConnected()
    {
        $redis = new \Redis();
        if (!@$redis->connect('127.0.0.1')) {
            $e = error_get_last();
            self::markTestSkipped($e['message']);
        }
        $xCast = <<<'EODUMP'
Redis {%A
  isConnected: true
  host: "127.0.0.1"
  port: 6379
  auth: null
  mode: ATOMIC
  dbNum: 0
  timeout: 0.0
  lastError: null
  persistentId: null
  options: {
    TCP_KEEPALIVE: 0
    READ_TIMEOUT: 0.0
    COMPRESSION: NONE
    SERIALIZER: NONE
    PREFIX: null
    SCAN: NORETRY
  }
}
EODUMP;
        $this->assertDumpMatchesFormat($xCast, $redis);
    }
}
