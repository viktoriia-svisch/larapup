<?php
namespace Illuminate\Contracts\Notifications;
interface Factory
{
    public function channel($name = null);
    public function send($notifiables, $notification);
    public function sendNow($notifiables, $notification);
}
