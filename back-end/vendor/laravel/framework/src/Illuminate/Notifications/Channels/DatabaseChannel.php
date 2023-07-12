<?php
namespace Illuminate\Notifications\Channels;
use RuntimeException;
use Illuminate\Notifications\Notification;
class DatabaseChannel
{
    public function send($notifiable, Notification $notification)
    {
        return $notifiable->routeNotificationFor('database', $notification)->create(
            $this->buildPayload($notifiable, $notification)
        );
    }
    protected function getData($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toDatabase')) {
            return is_array($data = $notification->toDatabase($notifiable))
                                ? $data : $data->data;
        }
        if (method_exists($notification, 'toArray')) {
            return $notification->toArray($notifiable);
        }
        throw new RuntimeException('Notification is missing toDatabase / toArray method.');
    }
    protected function buildPayload($notifiable, Notification $notification)
    {
        return [
            'id' => $notification->id,
            'type' => get_class($notification),
            'data' => $this->getData($notifiable, $notification),
            'read_at' => null,
        ];
    }
}
