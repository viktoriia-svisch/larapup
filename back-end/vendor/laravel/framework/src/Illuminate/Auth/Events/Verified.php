<?php
namespace Illuminate\Auth\Events;
use Illuminate\Queue\SerializesModels;
class Verified
{
    use SerializesModels;
    public $user;
    public function __construct($user)
    {
        $this->user = $user;
    }
}
