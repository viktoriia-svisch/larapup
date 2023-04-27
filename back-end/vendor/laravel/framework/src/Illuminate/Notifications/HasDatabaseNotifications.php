<?php
namespace Illuminate\Notifications;
trait HasDatabaseNotifications
{
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }
    public function readNotifications()
    {
        return $this->notifications()->whereNotNull('read_at');
    }
    public function unreadNotifications()
    {
        return $this->notifications()->whereNull('read_at');
    }
}
