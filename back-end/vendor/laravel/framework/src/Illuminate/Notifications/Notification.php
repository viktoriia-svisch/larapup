<?php
namespace Illuminate\Notifications;
use Illuminate\Queue\SerializesModels;
class Notification
{
    use SerializesModels;
    public $id;
    public $locale;
    public function broadcastOn()
    {
        return [];
    }
    public function locale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
}
