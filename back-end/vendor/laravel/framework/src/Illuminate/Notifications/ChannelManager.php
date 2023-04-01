<?php
namespace Illuminate\Notifications;
use InvalidArgumentException;
use Illuminate\Support\Manager;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Bus\Dispatcher as Bus;
use Illuminate\Contracts\Notifications\Factory as FactoryContract;
use Illuminate\Contracts\Notifications\Dispatcher as DispatcherContract;
class ChannelManager extends Manager implements DispatcherContract, FactoryContract
{
    protected $defaultChannel = 'mail';
    protected $locale;
    public function send($notifiables, $notification)
    {
        return (new NotificationSender(
            $this, $this->app->make(Bus::class), $this->app->make(Dispatcher::class), $this->locale)
        )->send($notifiables, $notification);
    }
    public function sendNow($notifiables, $notification, array $channels = null)
    {
        return (new NotificationSender(
            $this, $this->app->make(Bus::class), $this->app->make(Dispatcher::class), $this->locale)
        )->sendNow($notifiables, $notification, $channels);
    }
    public function channel($name = null)
    {
        return $this->driver($name);
    }
    protected function createDatabaseDriver()
    {
        return $this->app->make(Channels\DatabaseChannel::class);
    }
    protected function createBroadcastDriver()
    {
        return $this->app->make(Channels\BroadcastChannel::class);
    }
    protected function createMailDriver()
    {
        return $this->app->make(Channels\MailChannel::class);
    }
    protected function createDriver($driver)
    {
        try {
            return parent::createDriver($driver);
        } catch (InvalidArgumentException $e) {
            if (class_exists($driver)) {
                return $this->app->make($driver);
            }
            throw $e;
        }
    }
    public function getDefaultDriver()
    {
        return $this->defaultChannel;
    }
    public function deliversVia()
    {
        return $this->getDefaultDriver();
    }
    public function deliverVia($channel)
    {
        $this->defaultChannel = $channel;
    }
    public function locale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
}
