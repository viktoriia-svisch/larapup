<?php
namespace Illuminate\Notifications\Events;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
class NotificationFailed
{
    use Queueable, SerializesModels;
    public $notifiable;
    public $notification;
    public $channel;
    public $data = [];
    public function __construct($notifiable, $notification, $channel, $data = [])
    {
        $this->data = $data;
        $this->channel = $channel;
        $this->notifiable = $notifiable;
        $this->notification = $notification;
    }
}
