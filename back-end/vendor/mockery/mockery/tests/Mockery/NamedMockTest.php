<?php
require_once __DIR__.'/DummyClasses/Namespaced.php';
use Mockery\Adapter\Phpunit\MockeryTestCase;
use test\Mockery\Stubs\Animal;
use test\Mockery\Stubs\Habitat;
class NamedMockTest extends MockeryTestCase
{
    public function itCreatesANamedMock()
    {
        $mock = Mockery::namedMock("Mockery\Dave123");
        $this->assertInstanceOf("Mockery\Dave123", $mock);
    }
    public function itCreatesPassesFurtherArgumentsJustLikeMock()
    {
        $mock = Mockery::namedMock("Mockery\Dave456", "DateTime", array(
            "getDave" => "dave"
        ));
        $this->assertInstanceOf("DateTime", $mock);
        $this->assertEquals("dave", $mock->getDave());
    }
    public function itShouldThrowIfAttemptingToRedefineNamedMock()
    {
        $mock = Mockery::namedMock("Mockery\Dave7");
        $this->expectException(\Mockery\Exception::class);
        $this->expectExceptionMessage("The mock named 'Mockery\Dave7' has been already defined with a different mock configuration");
        $mock = Mockery::namedMock("Mockery\Dave7", "DateTime");
    }
    public function itCreatesConcreteMethodImplementationWithReturnType()
    {
        $cactus = new \Nature\Plant();
        $gardener = Mockery::namedMock(
            "NewNamespace\\ClassName",
            "Gardener",
            array('water' => true)
        );
        $this->assertTrue($gardener->water($cactus));
    }
    public function it_gracefully_handles_namespacing()
    {
        $animal = Mockery::namedMock(
            uniqid(Animal::class, false),
            Animal::class
        );
        $animal->shouldReceive("habitat")->andReturn(new Habitat());
        $this->assertInstanceOf(Habitat::class, $animal->habitat());
    }
}
