<?php
namespace Illuminate\Support\Testing\Fakes;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\PendingMail;
class PendingMailFake extends PendingMail
{
    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }
    public function send(Mailable $mailable)
    {
        return $this->sendNow($mailable);
    }
    public function sendNow(Mailable $mailable)
    {
        $this->mailer->send($this->fill($mailable));
    }
    public function queue(Mailable $mailable)
    {
        return $this->mailer->queue($this->fill($mailable));
    }
}
