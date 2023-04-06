<?php
namespace Illuminate\Support\Facades;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Testing\Fakes\EventFake;
class Event extends Facade
{
    public static function fake($eventsToFake = [])
    {
        static::swap($fake = new EventFake(static::getFacadeRoot(), $eventsToFake));
        Model::setEventDispatcher($fake);
    }
    public static function fakeFor(callable $callable, array $eventsToFake = [])
    {
        $originalDispatcher = static::getFacadeRoot();
        static::fake($eventsToFake);
        return tap($callable(), function () use ($originalDispatcher) {
            static::swap($originalDispatcher);
            Model::setEventDispatcher($originalDispatcher);
        });
    }
    protected static function getFacadeAccessor()
    {
        return 'events';
    }
}
