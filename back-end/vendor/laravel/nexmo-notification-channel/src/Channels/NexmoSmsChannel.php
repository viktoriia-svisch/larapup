<?php
namespace Illuminate\Notifications\Channels;
use Nexmo\Client as NexmoClient;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\NexmoMessage;
class NexmoSmsChannel
{
    protected $nexmo;
    protected $from;
    public function __construct(NexmoClient $nexmo, $from)
    {
        $this->from = $from;
        $this->nexmo = $nexmo;
    }
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('nexmo', $notification)) {
            return;
        }
        $message = $notification->toNexmo($notifiable);
        if (is_string($message)) {
            $message = new NexmoMessage($message);
        }
        return $this->nexmo->message()->send([
            'type' => $message->type,
            'from' => $message->from ?: $this->from,
            'to' => $to,
            'text' => trim($message->content),
        ]);
    }
}
