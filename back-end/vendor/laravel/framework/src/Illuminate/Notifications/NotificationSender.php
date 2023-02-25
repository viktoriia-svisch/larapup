<?php
namespace Illuminate\Notifications;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
class NotificationSender
{
    use Localizable;
    protected $manager;
    protected $bus;
    protected $events;
    protected $locale;
    public function __construct($manager, $bus, $events, $locale = null)
    {
        $this->bus = $bus;
        $this->events = $events;
        $this->manager = $manager;
        $this->locale = $locale;
    }
    public function send($notifiables, $notification)
    {
        $notifiables = $this->formatNotifiables($notifiables);
        if ($notification instanceof ShouldQueue) {
            return $this->queueNotification($notifiables, $notification);
        }
        return $this->sendNow($notifiables, $notification);
    }
    public function sendNow($notifiables, $notification, array $channels = null)
    {
        $notifiables = $this->formatNotifiables($notifiables);
        $original = clone $notification;
        foreach ($notifiables as $notifiable) {
            if (empty($viaChannels = $channels ?: $notification->via($notifiable))) {
                continue;
            }
            $this->withLocale($this->preferredLocale($notifiable, $notification), function () use ($viaChannels, $notifiable, $original) {
                $notificationId = Str::uuid()->toString();
                foreach ((array) $viaChannels as $channel) {
                    $this->sendToNotifiable($notifiable, $notificationId, clone $original, $channel);
                }
            });
        }
    }
    protected function preferredLocale($notifiable, $notification)
    {
        return $notification->locale ?? $this->locale ?? value(function () use ($notifiable) {
            if ($notifiable instanceof HasLocalePreference) {
                return $notifiable->preferredLocale();
            }
        });
    }
    protected function sendToNotifiable($notifiable, $id, $notification, $channel)
    {
        if (! $notification->id) {
            $notification->id = $id;
        }
        if (! $this->shouldSendNotification($notifiable, $notification, $channel)) {
            return;
        }
        $response = $this->manager->driver($channel)->send($notifiable, $notification);
        $this->events->dispatch(
            new Events\NotificationSent($notifiable, $notification, $channel, $response)
        );
    }
    protected function shouldSendNotification($notifiable, $notification, $channel)
    {
        return $this->events->until(
            new Events\NotificationSending($notifiable, $notification, $channel)
        ) !== false;
    }
    protected function queueNotification($notifiables, $notification)
    {
        $notifiables = $this->formatNotifiables($notifiables);
        $original = clone $notification;
        foreach ($notifiables as $notifiable) {
            $notificationId = Str::uuid()->toString();
            foreach ($original->via($notifiable) as $channel) {
                $notification = clone $original;
                $notification->id = $notificationId;
                if (! is_null($this->locale)) {
                    $notification->locale = $this->locale;
                }
                $this->bus->dispatch(
                    (new SendQueuedNotifications($notifiable, $notification, [$channel]))
                            ->onConnection($notification->connection)
                            ->onQueue($notification->queue)
                            ->delay($notification->delay)
                );
            }
        }
    }
    protected function formatNotifiables($notifiables)
    {
        if (! $notifiables instanceof Collection && ! is_array($notifiables)) {
            return $notifiables instanceof Model
                            ? new ModelCollection([$notifiables]) : [$notifiables];
        }
        return $notifiables;
    }
}
