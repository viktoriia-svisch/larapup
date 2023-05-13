<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
class MockingClassConstantsTest extends MockeryTestCase
{
    public function itShouldAllowToMockClassConstants()
    {
        \Mockery::getConfiguration()->setConstantsMap([
            'ClassWithConstants' => [
                'FOO' => 'baz',
                'X' => 2,
            ]
        ]);
        $mock = \Mockery::mock('overload:ClassWithConstants');
        self::assertEquals('baz', $mock::FOO);
        self::assertEquals(2, $mock::X);
    }
}
