<?php
namespace Mockery\Adapter\Phpunit\Legacy;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
class TestListenerForV7 implements TestListener
{
    use TestListenerDefaultImplementation;
    private $trait;
    public function __construct()
    {
        $this->trait = new TestListenerTrait();
    }
    public function endTest(Test $test, float $time): void
    {
        $this->trait->endTest($test, $time);
    }
    public function startTestSuite(TestSuite $suite): void
    {
        $this->trait->startTestSuite();
    }
}
