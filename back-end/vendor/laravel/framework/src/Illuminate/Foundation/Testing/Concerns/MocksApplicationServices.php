<?php
namespace Illuminate\Foundation\Testing\Concerns;
use Mockery;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcherContract;
use Illuminate\Contracts\Events\Dispatcher as EventsDispatcherContract;
use Illuminate\Contracts\Notifications\Dispatcher as NotificationDispatcher;
trait MocksApplicationServices
{
    protected $firedEvents = [];
    protected $firedModelEvents = [];
    protected $dispatchedJobs = [];
    protected $dispatchedNotifications = [];
    public function expectsEvents($events)
    {
        $events = is_array($events) ? $events : func_get_args();
        $this->withoutEvents();
        $this->beforeApplicationDestroyed(function () use ($events) {
            $fired = $this->getFiredEvents($events);
            $this->assertEmpty(
                $eventsNotFired = array_diff($events, $fired),
                'These expected events were not fired: ['.implode(', ', $eventsNotFired).']'
            );
        });
        return $this;
    }
    public function doesntExpectEvents($events)
    {
        $events = is_array($events) ? $events : func_get_args();
        $this->withoutEvents();
        $this->beforeApplicationDestroyed(function () use ($events) {
            $this->assertEmpty(
                $fired = $this->getFiredEvents($events),
                'These unexpected events were fired: ['.implode(', ', $fired).']'
            );
        });
        return $this;
    }
    protected function withoutEvents()
    {
        $mock = Mockery::mock(EventsDispatcherContract::class)->shouldIgnoreMissing();
        $mock->shouldReceive('dispatch')->andReturnUsing(function ($called) {
            $this->firedEvents[] = $called;
        });
        $this->app->instance('events', $mock);
        return $this;
    }
    protected function getFiredEvents(array $events)
    {
        return $this->getDispatched($events, $this->firedEvents);
    }
    protected function expectsJobs($jobs)
    {
        $jobs = is_array($jobs) ? $jobs : func_get_args();
        $this->withoutJobs();
        $this->beforeApplicationDestroyed(function () use ($jobs) {
            $dispatched = $this->getDispatchedJobs($jobs);
            $this->assertEmpty(
                $jobsNotDispatched = array_diff($jobs, $dispatched),
                'These expected jobs were not dispatched: ['.implode(', ', $jobsNotDispatched).']'
            );
        });
        return $this;
    }
    protected function doesntExpectJobs($jobs)
    {
        $jobs = is_array($jobs) ? $jobs : func_get_args();
        $this->withoutJobs();
        $this->beforeApplicationDestroyed(function () use ($jobs) {
            $this->assertEmpty(
                $dispatched = $this->getDispatchedJobs($jobs),
                'These unexpected jobs were dispatched: ['.implode(', ', $dispatched).']'
            );
        });
        return $this;
    }
    protected function withoutJobs()
    {
        $mock = Mockery::mock(BusDispatcherContract::class)->shouldIgnoreMissing();
        $mock->shouldReceive('dispatch', 'dispatchNow')->andReturnUsing(function ($dispatched) {
            $this->dispatchedJobs[] = $dispatched;
        });
        $this->app->instance(
            BusDispatcherContract::class, $mock
        );
        return $this;
    }
    protected function getDispatchedJobs(array $jobs)
    {
        return $this->getDispatched($jobs, $this->dispatchedJobs);
    }
    protected function getDispatched(array $classes, array $dispatched)
    {
        return array_filter($classes, function ($class) use ($dispatched) {
            return $this->wasDispatched($class, $dispatched);
        });
    }
    protected function wasDispatched($needle, array $haystack)
    {
        foreach ($haystack as $dispatched) {
            if ((is_string($dispatched) && ($dispatched === $needle || is_subclass_of($dispatched, $needle))) ||
                $dispatched instanceof $needle) {
                return true;
            }
        }
        return false;
    }
    protected function withoutNotifications()
    {
        $mock = Mockery::mock(NotificationDispatcher::class);
        $mock->shouldReceive('send')->andReturnUsing(function ($notifiable, $instance, $channels = []) {
            $this->dispatchedNotifications[] = compact(
                'notifiable', 'instance', 'channels'
            );
        });
        $this->app->instance(NotificationDispatcher::class, $mock);
        return $this;
    }
    protected function expectsNotification($notifiable, $notification)
    {
        $this->withoutNotifications();
        $this->beforeApplicationDestroyed(function () use ($notifiable, $notification) {
            foreach ($this->dispatchedNotifications as $dispatched) {
                $notified = $dispatched['notifiable'];
                if (($notified === $notifiable ||
                     $notified->getKey() == $notifiable->getKey()) &&
                    get_class($dispatched['instance']) === $notification
                ) {
                    return $this;
                }
            }
            $this->fail('The following expected notification were not dispatched: ['.$notification.']');
        });
        return $this;
    }
}
