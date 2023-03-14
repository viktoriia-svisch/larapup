<?php
namespace Illuminate\Notifications;
use Illuminate\Database\Eloquent\Collection;
class DatabaseNotificationCollection extends Collection
{
    public function markAsRead()
    {
        $this->each(function ($notification) {
            $notification->markAsRead();
        });
    }
    public function markAsUnread()
    {
        $this->each(function ($notification) {
            $notification->markAsUnread();
        });
    }
}
