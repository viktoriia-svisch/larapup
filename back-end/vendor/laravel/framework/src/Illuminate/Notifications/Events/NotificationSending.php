<?php
namespace Illuminate\Notifications\Events;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
class NotificationSending
{
    use Queueable, SerializesModels;
    public $notifiable;
    public $notification;
    public $channel;
    public function __construct($notifiable, $notification, $channel)
    {
        $this->channel = $channel;
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }
}
