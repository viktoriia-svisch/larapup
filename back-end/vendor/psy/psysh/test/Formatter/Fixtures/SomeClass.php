<?php
namespace Psy\Test\Formatter\Fixtures;
class SomeClass
{
    const SOME_CONST = 'some const';
    private $someProp = 'some prop';
    public function someMethod($someParam)
    {
        return 'some method';
    }
    public static function someClosure()
    {
        return function () {
            return 'some closure';
        };
    }
}
