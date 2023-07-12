<?php
namespace Illuminate\Notifications;
use Illuminate\Contracts\Notifications\Dispatcher;
class AnonymousNotifiable
{
    public $routes = [];
    public function route($channel, $route)
    {
        $this->routes[$channel] = $route;
        return $this;
    }
    public function notify($notification)
    {
        app(Dispatcher::class)->send($this, $notification);
    }
    public function notifyNow($notification)
    {
        app(Dispatcher::class)->sendNow($this, $notification);
    }
    public function routeNotificationFor($driver)
    {
        return $this->routes[$driver] ?? null;
    }
    public function getKey()
    {
    }
}
