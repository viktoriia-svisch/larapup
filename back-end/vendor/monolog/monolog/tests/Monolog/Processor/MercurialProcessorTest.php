<?php
namespace Monolog\Processor;
use Monolog\TestCase;
class MercurialProcessorTest extends TestCase
{
    public function testProcessor()
    {
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            exec("where hg 2>NUL", $output, $result);
        } else {
            exec("which hg 2>/dev/null >/dev/null", $output, $result);
        }
        if ($result != 0) {
            $this->markTestSkipped('hg is missing');
            return;
        }
        `hg init`;
        $processor = new MercurialProcessor();
        $record = $processor($this->getRecord());
        $this->assertArrayHasKey('hg', $record['extra']);
        $this->assertTrue(!is_array($record['extra']['hg']['branch']));
        $this->assertTrue(!is_array($record['extra']['hg']['revision']));
    }
}
