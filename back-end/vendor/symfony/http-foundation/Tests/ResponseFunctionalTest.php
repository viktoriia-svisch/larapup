<?php
namespace Symfony\Component\HttpFoundation\Tests;
use PHPUnit\Framework\TestCase;
class ResponseFunctionalTest extends TestCase
{
    private static $server;
    public static function setUpBeforeClass()
    {
        $spec = [
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w'],
        ];
        if (!self::$server = @proc_open('exec php -S localhost:8054', $spec, $pipes, __DIR__.'/Fixtures/response-functional')) {
            self::markTestSkipped('PHP server unable to start.');
        }
        sleep(1);
    }
    public static function tearDownAfterClass()
    {
        if (self::$server) {
            proc_terminate(self::$server);
            proc_close(self::$server);
        }
    }
    public function testCookie($fixture)
    {
        $result = file_get_contents(sprintf('http:
        $this->assertStringMatchesFormatFile(__DIR__.sprintf('/Fixtures/response-functional/%s.expected', $fixture), $result);
    }
    public function provideCookie()
    {
        foreach (glob(__DIR__.'/Fixtures/response-functional/*.php') as $file) {
            yield [pathinfo($file, PATHINFO_FILENAME)];
        }
    }
}
