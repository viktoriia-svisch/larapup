<?php
namespace Symfony\Component\VarDumper\Tests\Caster;
use PHPUnit\Framework\TestCase;
use Symfony\Component\VarDumper\Test\VarDumperTestTrait;
class MemcachedCasterTest extends TestCase
{
    use VarDumperTestTrait;
    public function testCastMemcachedWithDefaultOptions()
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached not available');
        }
        $var = new \Memcached();
        $var->addServer('127.0.0.1', 11211);
        $var->addServer('127.0.0.2', 11212);
        $expected = <<<EOTXT
Memcached {
  servers: array:2 [
    0 => array:3 [
      "host" => "127.0.0.1"
      "port" => 11211
      "type" => "TCP"
    ]
    1 => array:3 [
      "host" => "127.0.0.2"
      "port" => 11212
      "type" => "TCP"
    ]
  ]
  options: {}
}
EOTXT;
        $this->assertDumpEquals($expected, $var);
    }
    public function testCastMemcachedWithCustomOptions()
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped('Memcached not available');
        }
        $var = new \Memcached();
        $var->addServer('127.0.0.1', 11211);
        $var->addServer('127.0.0.2', 11212);
        $var->setOption(\Memcached::OPT_COMPRESSION, false);
        $var->setOption(\Memcached::OPT_PREFIX_KEY, 'pre');
        $var->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
        $expected = <<<'EOTXT'
Memcached {
  servers: array:2 [
    0 => array:3 [
      "host" => "127.0.0.1"
      "port" => 11211
      "type" => "TCP"
    ]
    1 => array:3 [
      "host" => "127.0.0.2"
      "port" => 11212
      "type" => "TCP"
    ]
  ]
  options: {
    OPT_COMPRESSION: false
    OPT_PREFIX_KEY: "pre"
    OPT_DISTRIBUTION: 1
  }
}
EOTXT;
        $this->assertDumpEquals($expected, $var);
    }
}
