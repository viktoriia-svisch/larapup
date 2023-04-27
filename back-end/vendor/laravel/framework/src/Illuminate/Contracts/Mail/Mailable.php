<?php
namespace Illuminate\Contracts\Mail;
use Illuminate\Contracts\Queue\Factory as Queue;
interface Mailable
{
    public function send(Mailer $mailer);
    public function queue(Queue $queue);
    public function later($delay, Queue $queue);
}
