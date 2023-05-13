<?php
namespace Illuminate\Notifications;
use Illuminate\Database\Eloquent\Model;
class DatabaseNotification extends Model
{
    public $incrementing = false;
    protected $table = 'notifications';
    protected $guarded = [];
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];
    public function notifiable()
    {
        return $this->morphTo();
    }
    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->forceFill(['read_at' => $this->freshTimestamp()])->save();
        }
    }
    public function markAsUnread()
    {
        if (! is_null($this->read_at)) {
            $this->forceFill(['read_at' => null])->save();
        }
    }
    public function read()
    {
        return $this->read_at !== null;
    }
    public function unread()
    {
        return $this->read_at === null;
    }
    public function newCollection(array $models = [])
    {
        return new DatabaseNotificationCollection($models);
    }
}
