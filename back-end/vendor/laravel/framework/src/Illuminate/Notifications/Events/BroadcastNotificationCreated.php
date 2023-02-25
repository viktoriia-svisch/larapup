<?php
namespace Illuminate\Notifications\Events;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
class BroadcastNotificationCreated implements ShouldBroadcast
{
    use Queueable, SerializesModels;
    public $notifiable;
    public $notification;
    public $data = [];
    public function __construct($notifiable, $notification, $data)
    {
        $this->data = $data;
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }
    public function broadcastOn()
    {
        $channels = $this->notification->broadcastOn();
        if (! empty($channels)) {
            return $channels;
        }
        return [new PrivateChannel($this->channelName())];
    }
    protected function channelName()
    {
        if (method_exists($this->notifiable, 'receivesBroadcastNotificationsOn')) {
            return $this->notifiable->receivesBroadcastNotificationsOn($this->notification);
        }
        $class = str_replace('\\', '.', get_class($this->notifiable));
        return $class.'.'.$this->notifiable->getKey();
    }
    public function broadcastWith()
    {
        return array_merge($this->data, [
            'id' => $this->notification->id,
            'type' => $this->broadcastType(),
        ]);
    }
    public function broadcastType()
    {
        return method_exists($this->notification, 'broadcastType')
                    ? $this->notification->broadcastType()
                    : get_class($this->notification);
    }
}
