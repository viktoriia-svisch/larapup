<?php
namespace Illuminate\Notifications;
use Illuminate\Support\Str;
use Illuminate\Contracts\Notifications\Dispatcher;
trait RoutesNotifications
{
    public function notify($instance)
    {
        app(Dispatcher::class)->send($this, $instance);
    }
    public function notifyNow($instance, array $channels = null)
    {
        app(Dispatcher::class)->sendNow($this, $instance, $channels);
    }
    public function routeNotificationFor($driver, $notification = null)
    {
        if (method_exists($this, $method = 'routeNotificationFor'.Str::studly($driver))) {
            return $this->{$method}($notification);
        }
        switch ($driver) {
            case 'database':
                return $this->notifications();
            case 'mail':
                return $this->email;
            case 'nexmo':
                return $this->phone_number;
        }
    }
}
