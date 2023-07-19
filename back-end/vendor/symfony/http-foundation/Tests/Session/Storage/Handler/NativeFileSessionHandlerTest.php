<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
class NativeFileSessionHandlerTest extends TestCase
{
    public function testConstruct()
    {
        $storage = new NativeSessionStorage(['name' => 'TESTING'], new NativeFileSessionHandler(sys_get_temp_dir()));
        $this->assertEquals('user', ini_get('session.save_handler'));
        $this->assertEquals(sys_get_temp_dir(), ini_get('session.save_path'));
        $this->assertEquals('TESTING', ini_get('session.name'));
    }
    public function testConstructSavePath($savePath, $expectedSavePath, $path)
    {
        $handler = new NativeFileSessionHandler($savePath);
        $this->assertEquals($expectedSavePath, ini_get('session.save_path'));
        $this->assertTrue(is_dir(realpath($path)));
        rmdir($path);
    }
    public function savePathDataProvider()
    {
        $base = sys_get_temp_dir();
        return [
            ["$base/foo", "$base/foo", "$base/foo"],
            ["5;$base/foo", "5;$base/foo", "$base/foo"],
            ["5;0600;$base/foo", "5;0600;$base/foo", "$base/foo"],
        ];
    }
    public function testConstructException()
    {
        $handler = new NativeFileSessionHandler('something;invalid;with;too-many-args');
    }
    public function testConstructDefault()
    {
        $path = ini_get('session.save_path');
        $storage = new NativeSessionStorage(['name' => 'TESTING'], new NativeFileSessionHandler());
        $this->assertEquals($path, ini_get('session.save_path'));
    }
}