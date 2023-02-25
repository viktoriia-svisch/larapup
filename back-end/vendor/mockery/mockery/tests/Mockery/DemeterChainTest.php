<?php
if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    require_once __DIR__.'/DummyClasses/DemeterChain.php';
}
use Mockery\Adapter\Phpunit\MockeryTestCase;
class DemeterChainTest extends MockeryTestCase
{
    private $mock;
    public function mockeryTestSetUp()
    {
        $this->mock = $this->mock = Mockery::mock()->shouldIgnoreMissing();
    }
    public function mockeryTestTearDown()
    {
        $this->mock->mockery_getContainer()->mockery_close();
    }
    public function testTwoChains()
    {
        $this->mock->shouldReceive('getElement->getFirst')
            ->once()
            ->andReturn('something');
        $this->mock->shouldReceive('getElement->getSecond')
            ->once()
            ->andReturn('somethingElse');
        $this->assertEquals(
            'something',
            $this->mock->getElement()->getFirst()
        );
        $this->assertEquals(
            'somethingElse',
            $this->mock->getElement()->getSecond()
        );
        $this->mock->mockery_getContainer()->mockery_close();
    }
    public function testTwoChainsWithExpectedParameters()
    {
        $this->mock->shouldReceive('getElement->getFirst')
            ->once()
            ->with('parameter')
            ->andReturn('something');
        $this->mock->shouldReceive('getElement->getSecond')
            ->once()
            ->with('secondParameter')
            ->andReturn('somethingElse');
        $this->assertEquals(
            'something',
            $this->mock->getElement()->getFirst('parameter')
        );
        $this->assertEquals(
            'somethingElse',
            $this->mock->getElement()->getSecond('secondParameter')
        );
        $this->mock->mockery_getContainer()->mockery_close();
    }
    public function testThreeChains()
    {
        $this->mock->shouldReceive('getElement->getFirst')
            ->once()
            ->andReturn('something');
        $this->mock->shouldReceive('getElement->getSecond')
            ->once()
            ->andReturn('somethingElse');
        $this->assertEquals(
            'something',
            $this->mock->getElement()->getFirst()
        );
        $this->assertEquals(
            'somethingElse',
            $this->mock->getElement()->getSecond()
        );
        $this->mock->shouldReceive('getElement->getFirst')
            ->once()
            ->andReturn('somethingNew');
        $this->assertEquals(
            'somethingNew',
            $this->mock->getElement()->getFirst()
        );
    }
    public function testManyChains()
    {
        $this->mock->shouldReceive('getElements->getFirst')
            ->once()
            ->andReturn('something');
        $this->mock->shouldReceive('getElements->getSecond')
            ->once()
            ->andReturn('somethingElse');
        $this->mock->getElements()->getFirst();
        $this->mock->getElements()->getSecond();
    }
    public function testTwoNotRelatedChains()
    {
        $this->mock->shouldReceive('getElement->getFirst')
            ->once()
            ->andReturn('something');
        $this->mock->shouldReceive('getOtherElement->getSecond')
            ->once()
            ->andReturn('somethingElse');
        $this->assertEquals(
            'somethingElse',
            $this->mock->getOtherElement()->getSecond()
        );
        $this->assertEquals(
            'something',
            $this->mock->getElement()->getFirst()
        );
    }
    public function testDemeterChain()
    {
        $this->mock->shouldReceive('getElement->getFirst')
            ->once()
            ->andReturn('somethingElse');
        $this->assertEquals('somethingElse', $this->mock->getElement()->getFirst());
    }
    public function testMultiLevelDemeterChain()
    {
        $this->mock->shouldReceive('levelOne->levelTwo->getFirst')
            ->andReturn('first');
        $this->mock->shouldReceive('levelOne->levelTwo->getSecond')
            ->andReturn('second');
        $this->assertEquals(
            'second',
            $this->mock->levelOne()->levelTwo()->getSecond()
        );
        $this->assertEquals(
            'first',
            $this->mock->levelOne()->levelTwo()->getFirst()
        );
    }
    public function testSimilarDemeterChainsOnDifferentClasses()
    {
        $mock1 = Mockery::mock('overload:mock1');
        $mock1->shouldReceive('select->some->data')->andReturn(1);
        $mock1->shouldReceive('select->some->other->data')->andReturn(2);
        $mock2 = Mockery::mock('overload:mock2');
        $mock2->shouldReceive('select->some->data')->andReturn(3);
        $mock2->shouldReceive('select->some->other->data')->andReturn(4);
        $this->assertEquals(1, mock1::select()->some()->data());
        $this->assertEquals(2, mock1::select()->some()->other()->data());
        $this->assertEquals(3, mock2::select()->some()->data());
        $this->assertEquals(4, mock2::select()->some()->other()->data());
    }
    public function testDemeterChainsWithClassReturnTypeHints()
    {
        $a = \Mockery::mock(\DemeterChain\A::class);
        $a->shouldReceive('foo->bar->baz')->andReturn(new stdClass);
        $m = new \DemeterChain\Main();
        $result = $m->callDemeter($a);
        $this->assertInstanceOf(stdClass::class, $result);
    }
}
