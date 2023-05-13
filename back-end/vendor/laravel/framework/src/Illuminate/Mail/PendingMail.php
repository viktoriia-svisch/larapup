<?php
namespace Illuminate\Mail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Translation\HasLocalePreference;
class PendingMail
{
    protected $mailer;
    protected $locale;
    protected $to = [];
    protected $cc = [];
    protected $bcc = [];
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }
    public function locale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
    public function to($users)
    {
        $this->to = $users;
        if (! $this->locale && $users instanceof HasLocalePreference) {
            $this->locale($users->preferredLocale());
        }
        return $this;
    }
    public function cc($users)
    {
        $this->cc = $users;
        return $this;
    }
    public function bcc($users)
    {
        $this->bcc = $users;
        return $this;
    }
    public function send(Mailable $mailable)
    {
        if ($mailable instanceof ShouldQueue) {
            return $this->queue($mailable);
        }
        return $this->mailer->send($this->fill($mailable));
    }
    public function sendNow(Mailable $mailable)
    {
        return $this->mailer->send($this->fill($mailable));
    }
    public function queue(Mailable $mailable)
    {
        $mailable = $this->fill($mailable);
        if (isset($mailable->delay)) {
            return $this->mailer->later($mailable->delay, $mailable);
        }
        return $this->mailer->queue($mailable);
    }
    public function later($delay, Mailable $mailable)
    {
        return $this->mailer->later($delay, $this->fill($mailable));
    }
    protected function fill(Mailable $mailable)
    {
        return $mailable->to($this->to)
                        ->cc($this->cc)
                        ->bcc($this->bcc)
                        ->locale($this->locale);
    }
}
