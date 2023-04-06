<?php
namespace Mockery\Adapter\Phpunit\Legacy;
class TestListenerForV5 extends \PHPUnit_Framework_BaseTestListener
{
    private $trait;
    public function __construct()
    {
        $this->trait = new TestListenerTrait();
    }
    public function endTest(\PHPUnit_Framework_Test $test, $time)
    {
        $this->trait->endTest($test, $time);
    }
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->trait->startTestSuite();
    }
}
