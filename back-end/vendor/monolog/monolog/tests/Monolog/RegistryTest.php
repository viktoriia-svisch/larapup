<?php
namespace Monolog;
class RegistryTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Registry::clear();
    }
    public function testHasLogger(array $loggersToAdd, array $loggersToCheck, array $expectedResult)
    {
        foreach ($loggersToAdd as $loggerToAdd) {
            Registry::addLogger($loggerToAdd);
        }
        foreach ($loggersToCheck as $index => $loggerToCheck) {
            $this->assertSame($expectedResult[$index], Registry::hasLogger($loggerToCheck));
        }
    }
    public function hasLoggerProvider()
    {
        $logger1 = new Logger('test1');
        $logger2 = new Logger('test2');
        $logger3 = new Logger('test3');
        return array(
            array(
                array($logger1),
                array($logger1, $logger2),
                array(true, false),
            ),
            array(
                array($logger1),
                array('test1', 'test2'),
                array(true, false),
            ),
            array(
                array($logger1, $logger2),
                array('test1', $logger2, 'test3', $logger3),
                array(true, true, false, false),
            ),
        );
    }
    public function testClearClears()
    {
        Registry::addLogger(new Logger('test1'), 'log');
        Registry::clear();
        $this->setExpectedException('\InvalidArgumentException');
        Registry::getInstance('log');
    }
    public function testRemovesLogger($loggerToAdd, $remove)
    {
        Registry::addLogger($loggerToAdd);
        Registry::removeLogger($remove);
        $this->setExpectedException('\InvalidArgumentException');
        Registry::getInstance($loggerToAdd->getName());
    }
    public function removedLoggerProvider()
    {
        $logger1 = new Logger('test1');
        return array(
            array($logger1, $logger1),
            array($logger1, 'test1'),
        );
    }
    public function testGetsSameLogger()
    {
        $logger1 = new Logger('test1');
        $logger2 = new Logger('test2');
        Registry::addLogger($logger1, 'test1');
        Registry::addLogger($logger2);
        $this->assertSame($logger1, Registry::getInstance('test1'));
        $this->assertSame($logger2, Registry::test2());
    }
    public function testFailsOnNonExistantLogger()
    {
        Registry::getInstance('test1');
    }
    public function testReplacesLogger()
    {
        $log1 = new Logger('test1');
        $log2 = new Logger('test2');
        Registry::addLogger($log1, 'log');
        Registry::addLogger($log2, 'log', true);
        $this->assertSame($log2, Registry::getInstance('log'));
    }
    public function testFailsOnUnspecifiedReplacement()
    {
        $log1 = new Logger('test1');
        $log2 = new Logger('test2');
        Registry::addLogger($log1, 'log');
        Registry::addLogger($log2, 'log');
    }
}
