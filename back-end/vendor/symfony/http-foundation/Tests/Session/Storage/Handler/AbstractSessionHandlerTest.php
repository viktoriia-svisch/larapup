<?php
namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;
use PHPUnit\Framework\TestCase;
class AbstractSessionHandlerTest extends TestCase
{
    private static $server;
    public static function setUpBeforeClass()
    {
        $spec = [
            1 => ['file', '/dev/null', 'w'],
            2 => ['file', '/dev/null', 'w'],
        ];
        if (!self::$server = @proc_open('exec php -S localhost:8053', $spec, $pipes, __DIR__.'/Fixtures')) {
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
    public function testSession($fixture)
    {
        $context = ['http' => ['header' => "Cookie: sid=123abc\r\n"]];
        $context = stream_context_create($context);
        $result = file_get_contents(sprintf('http:
        $this->assertStringEqualsFile(__DIR__.sprintf('/Fixtures/%s.expected', $fixture), $result);
    }
    public function provideSession()
    {
        foreach (glob(__DIR__.'/Fixtures/*.php') as $file) {
            yield [pathinfo($file, PATHINFO_FILENAME)];
        }
    }
}
