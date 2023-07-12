<?php
namespace test\Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Loader\RequireLoader;
class TraitTest extends MockeryTestCase
{
    public function it_can_create_an_object_for_a_simple_trait()
    {
        $trait = mock(SimpleTrait::class);
        $this->assertEquals('bar', $trait->foo());
    }
    public function it_creates_abstract_methods_as_necessary()
    {
        $trait = mock(TraitWithAbstractMethod::class, ['doBaz' => 'baz']);
        $this->assertEquals('baz', $trait->baz());
    }
    public function it_can_create_an_object_using_multiple_traits()
    {
        $trait = mock(SimpleTrait::class, TraitWithAbstractMethod::class, [
            'doBaz' => 123,
        ]);
        $this->assertEquals('bar', $trait->foo());
        $this->assertEquals(123, $trait->baz());
    }
}
trait SimpleTrait
{
    public function foo()
    {
        return 'bar';
    }
}
trait TraitWithAbstractMethod
{
    public function baz()
    {
        return $this->doBaz();
    }
    abstract public function doBaz();
}
