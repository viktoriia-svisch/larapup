<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingAllLowerCasedMethodsTest extends MockeryTestCase
{
    public function itShouldAllowToCallAllLowerCasedMethodAsCamelCased()
    {
        require __DIR__."/Fixtures/ClassWithAllLowerCaseMethod.php";
        $mock = mock('test\Mockery\Fixtures\ClassWithAllLowerCaseMethod');
        $mock->shouldReceive('userExpectsCamelCaseMethod')
            ->andReturn('mocked');
        $result = $mock->userExpectsCamelCaseMethod();
        $expected = 'mocked';
        self::assertSame($expected, $result);
    }
}
