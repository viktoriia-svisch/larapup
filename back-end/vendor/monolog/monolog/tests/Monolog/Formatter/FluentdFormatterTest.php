<?php
namespace Monolog\Formatter;
use Monolog\Logger;
use Monolog\TestCase;
class FluentdFormatterTest extends TestCase
{
    public function testConstruct()
    {
        $formatter = new FluentdFormatter();
        $this->assertEquals(false, $formatter->isUsingLevelsInTag());
        $formatter = new FluentdFormatter(false);
        $this->assertEquals(false, $formatter->isUsingLevelsInTag());
        $formatter = new FluentdFormatter(true);
        $this->assertEquals(true, $formatter->isUsingLevelsInTag());
    }
    public function testFormat()
    {
        $record = $this->getRecord(Logger::WARNING);
        $record['datetime'] = new \DateTime("@0");
        $formatter = new FluentdFormatter();
        $this->assertEquals(
            '["test",0,{"message":"test","context":[],"extra":[],"level":300,"level_name":"WARNING"}]',
            $formatter->format($record)
        );
    }
    public function testFormatWithTag()
    {
        $record = $this->getRecord(Logger::ERROR);
        $record['datetime'] = new \DateTime("@0");
        $formatter = new FluentdFormatter(true);
        $this->assertEquals(
            '["test.error",0,{"message":"test","context":[],"extra":[]}]',
            $formatter->format($record)
        );
    }
}
