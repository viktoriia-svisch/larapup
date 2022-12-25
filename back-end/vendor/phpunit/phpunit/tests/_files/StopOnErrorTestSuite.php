<?php
class StopOnErrorTestSuite extends \PHPUnit\Framework\TestCase
{
    public function testIncomplete()
    {
        $this->markTestIncomplete();
    }
    public function testWithError()
    {
        $this->assertTrue(true);
        throw new Error('StopOnErrorTestSuite_error');
    }
    public function testThatIsNeverReached()
    {
        $this->assertTrue(true);
    }
}
