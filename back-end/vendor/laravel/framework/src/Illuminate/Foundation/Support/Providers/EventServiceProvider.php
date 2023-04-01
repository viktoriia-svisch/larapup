<?php
namespace Illuminate\Foundation\Support\Providers;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];
    protected $subscribe = [];
    public function boot()
    {
        foreach ($this->listens() as $event => $listeners) {
            foreach ($listeners as $listener) {
                Event::listen($event, $listener);
            }
        }
        foreach ($this->subscribe as $subscriber) {
            Event::subscribe($subscriber);
        }
    }
    public function register()
    {
    }
    public function listens()
    {
        return $this->listen;
    }
}
