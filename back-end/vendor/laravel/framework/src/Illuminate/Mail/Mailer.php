<?php
namespace Illuminate\Mail;
use Swift_Mailer;
use InvalidArgumentException;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\Factory as QueueContract;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Mail\MailQueue as MailQueueContract;
class Mailer implements MailerContract, MailQueueContract
{
    use Macroable;
    protected $views;
    protected $swift;
    protected $events;
    protected $from;
    protected $replyTo;
    protected $to;
    protected $queue;
    protected $failedRecipients = [];
    public function __construct(Factory $views, Swift_Mailer $swift, Dispatcher $events = null)
    {
        $this->views = $views;
        $this->swift = $swift;
        $this->events = $events;
    }
    public function alwaysFrom($address, $name = null)
    {
        $this->from = compact('address', 'name');
    }
    public function alwaysReplyTo($address, $name = null)
    {
        $this->replyTo = compact('address', 'name');
    }
    public function alwaysTo($address, $name = null)
    {
        $this->to = compact('address', 'name');
    }
    public function to($users)
    {
        return (new PendingMail($this))->to($users);
    }
    public function cc($users)
    {
        return (new PendingMail($this))->cc($users);
    }
    public function bcc($users)
    {
        return (new PendingMail($this))->bcc($users);
    }
    public function html($html, $callback)
    {
        return $this->send(['html' => new HtmlString($html)], [], $callback);
    }
    public function raw($text, $callback)
    {
        return $this->send(['raw' => $text], [], $callback);
    }
    public function plain($view, array $data, $callback)
    {
        return $this->send(['text' => $view], $data, $callback);
    }
    public function render($view, array $data = [])
    {
        [$view, $plain, $raw] = $this->parseView($view);
        $data['message'] = $this->createMessage();
        return $this->renderView($view ?: $plain, $data);
    }
    public function send($view, array $data = [], $callback = null)
    {
        if ($view instanceof MailableContract) {
            return $this->sendMailable($view);
        }
        [$view, $plain, $raw] = $this->parseView($view);
        $data['message'] = $message = $this->createMessage();
        call_user_func($callback, $message);
        $this->addContent($message, $view, $plain, $raw, $data);
        if (isset($this->to['address'])) {
            $this->setGlobalToAndRemoveCcAndBcc($message);
        }
        $swiftMessage = $message->getSwiftMessage();
        if ($this->shouldSendMessage($swiftMessage, $data)) {
            $this->sendSwiftMessage($swiftMessage);
            $this->dispatchSentEvent($message, $data);
        }
    }
    protected function sendMailable(MailableContract $mailable)
    {
        return $mailable instanceof ShouldQueue
            ? $mailable->queue($this->queue) : $mailable->send($this);
    }
    protected function parseView($view)
    {
        if (is_string($view)) {
            return [$view, null, null];
        }
        if (is_array($view) && isset($view[0])) {
            return [$view[0], $view[1], null];
        }
        if (is_array($view)) {
            return [
                $view['html'] ?? null,
                $view['text'] ?? null,
                $view['raw'] ?? null,
            ];
        }
        throw new InvalidArgumentException('Invalid view.');
    }
    protected function addContent($message, $view, $plain, $raw, $data)
    {
        if (isset($view)) {
            $message->setBody($this->renderView($view, $data), 'text/html');
        }
        if (isset($plain)) {
            $method = isset($view) ? 'addPart' : 'setBody';
            $message->$method($this->renderView($plain, $data), 'text/plain');
        }
        if (isset($raw)) {
            $method = (isset($view) || isset($plain)) ? 'addPart' : 'setBody';
            $message->$method($raw, 'text/plain');
        }
    }
    protected function renderView($view, $data)
    {
        return $view instanceof Htmlable
                        ? $view->toHtml()
                        : $this->views->make($view, $data)->render();
    }
    protected function setGlobalToAndRemoveCcAndBcc($message)
    {
        $message->to($this->to['address'], $this->to['name'], true);
        $message->cc(null, null, true);
        $message->bcc(null, null, true);
    }
    public function queue($view, $queue = null)
    {
        if (! $view instanceof MailableContract) {
            throw new InvalidArgumentException('Only mailables may be queued.');
        }
        if (is_string($queue)) {
            $view->onQueue($queue);
        }
        return $view->queue($this->queue);
    }
    public function onQueue($queue, $view)
    {
        return $this->queue($view, $queue);
    }
    public function queueOn($queue, $view)
    {
        return $this->onQueue($queue, $view);
    }
    public function later($delay, $view, $queue = null)
    {
        if (! $view instanceof MailableContract) {
            throw new InvalidArgumentException('Only mailables may be queued.');
        }
        return $view->later($delay, is_null($queue) ? $this->queue : $queue);
    }
    public function laterOn($queue, $delay, $view)
    {
        return $this->later($delay, $view, $queue);
    }
    protected function createMessage()
    {
        $message = new Message($this->swift->createMessage('message'));
        if (! empty($this->from['address'])) {
            $message->from($this->from['address'], $this->from['name']);
        }
        if (! empty($this->replyTo['address'])) {
            $message->replyTo($this->replyTo['address'], $this->replyTo['name']);
        }
        return $message;
    }
    protected function sendSwiftMessage($message)
    {
        try {
            return $this->swift->send($message, $this->failedRecipients);
        } finally {
            $this->forceReconnection();
        }
    }
    protected function shouldSendMessage($message, $data = [])
    {
        if (! $this->events) {
            return true;
        }
        return $this->events->until(
            new Events\MessageSending($message, $data)
        ) !== false;
    }
    protected function dispatchSentEvent($message, $data = [])
    {
        if ($this->events) {
            $this->events->dispatch(
                new Events\MessageSent($message->getSwiftMessage(), $data)
            );
        }
    }
    protected function forceReconnection()
    {
        $this->getSwiftMailer()->getTransport()->stop();
    }
    public function getViewFactory()
    {
        return $this->views;
    }
    public function getSwiftMailer()
    {
        return $this->swift;
    }
    public function failures()
    {
        return $this->failedRecipients;
    }
    public function setSwiftMailer($swift)
    {
        $this->swift = $swift;
    }
    public function setQueue(QueueContract $queue)
    {
        $this->queue = $queue;
        return $this;
    }
}
