<?php
namespace Mockery\Adapter\Phpunit\Legacy;
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
class TestListenerForV6 extends BaseTestListener
{
    private $trait;
    public function __construct()
    {
        $this->trait = new TestListenerTrait();
    }
    public function endTest(Test $test, $time)
    {
        $this->trait->endTest($test, $time);
    }
    public function startTestSuite(TestSuite $suite)
    {
        $this->trait->startTestSuite();
    }
}
