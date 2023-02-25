<?php
namespace Illuminate\Mail;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
class SendQueuedMailable
{
    public $mailable;
    public $tries;
    public $timeout;
    public function __construct(MailableContract $mailable)
    {
        $this->mailable = $mailable;
        $this->tries = property_exists($mailable, 'tries') ? $mailable->tries : null;
        $this->timeout = property_exists($mailable, 'timeout') ? $mailable->timeout : null;
    }
    public function handle(MailerContract $mailer)
    {
        $this->mailable->send($mailer);
    }
    public function displayName()
    {
        return get_class($this->mailable);
    }
    public function failed($e)
    {
        if (method_exists($this->mailable, 'failed')) {
            $this->mailable->failed($e);
        }
    }
    public function __clone()
    {
        $this->mailable = clone $this->mailable;
    }
}
