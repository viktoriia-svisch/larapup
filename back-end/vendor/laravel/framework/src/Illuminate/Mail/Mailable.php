<?php
namespace Illuminate\Mail;
use ReflectionClass;
use ReflectionProperty;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Container\Container;
use Illuminate\Support\Traits\Localizable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Contracts\Queue\Factory as Queue;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Mail\Mailable as MailableContract;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
class Mailable implements MailableContract, Renderable
{
    use ForwardsCalls, Localizable;
    public $locale;
    public $from = [];
    public $to = [];
    public $cc = [];
    public $bcc = [];
    public $replyTo = [];
    public $subject;
    protected $markdown;
    protected $html;
    public $view;
    public $textView;
    public $viewData = [];
    public $attachments = [];
    public $rawAttachments = [];
    public $diskAttachments = [];
    public $callbacks = [];
    public static $viewDataCallback;
    public function send(MailerContract $mailer)
    {
        $this->withLocale($this->locale, function () use ($mailer) {
            Container::getInstance()->call([$this, 'build']);
            $mailer->send($this->buildView(), $this->buildViewData(), function ($message) {
                $this->buildFrom($message)
                     ->buildRecipients($message)
                     ->buildSubject($message)
                     ->runCallbacks($message)
                     ->buildAttachments($message);
            });
        });
    }
    public function queue(Queue $queue)
    {
        if (isset($this->delay)) {
            return $this->later($this->delay, $queue);
        }
        $connection = property_exists($this, 'connection') ? $this->connection : null;
        $queueName = property_exists($this, 'queue') ? $this->queue : null;
        return $queue->connection($connection)->pushOn(
            $queueName ?: null, new SendQueuedMailable($this)
        );
    }
    public function later($delay, Queue $queue)
    {
        $connection = property_exists($this, 'connection') ? $this->connection : null;
        $queueName = property_exists($this, 'queue') ? $this->queue : null;
        return $queue->connection($connection)->laterOn(
            $queueName ?: null, $delay, new SendQueuedMailable($this)
        );
    }
    public function render()
    {
        return $this->withLocale($this->locale, function () {
            Container::getInstance()->call([$this, 'build']);
            return Container::getInstance()->make('mailer')->render(
                $this->buildView(), $this->buildViewData()
            );
        });
    }
    protected function buildView()
    {
        if (isset($this->html)) {
            return array_filter([
                'html' => new HtmlString($this->html),
                'text' => $this->textView ?? null,
            ]);
        }
        if (isset($this->markdown)) {
            return $this->buildMarkdownView();
        }
        if (isset($this->view, $this->textView)) {
            return [$this->view, $this->textView];
        } elseif (isset($this->textView)) {
            return ['text' => $this->textView];
        }
        return $this->view;
    }
    protected function buildMarkdownView()
    {
        $markdown = Container::getInstance()->make(Markdown::class);
        if (isset($this->theme)) {
            $markdown->theme($this->theme);
        }
        $data = $this->buildViewData();
        return [
            'html' => $markdown->render($this->markdown, $data),
            'text' => $this->buildMarkdownText($markdown, $data),
        ];
    }
    public function buildViewData()
    {
        $data = $this->viewData;
        if (static::$viewDataCallback) {
            $data = array_merge($data, call_user_func(static::$viewDataCallback, $this));
        }
        foreach ((new ReflectionClass($this))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getDeclaringClass()->getName() !== self::class) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }
        return $data;
    }
    protected function buildMarkdownText($markdown, $data)
    {
        return $this->textView
                ?? $markdown->renderText($this->markdown, $data);
    }
    protected function buildFrom($message)
    {
        if (! empty($this->from)) {
            $message->from($this->from[0]['address'], $this->from[0]['name']);
        }
        return $this;
    }
    protected function buildRecipients($message)
    {
        foreach (['to', 'cc', 'bcc', 'replyTo'] as $type) {
            foreach ($this->{$type} as $recipient) {
                $message->{$type}($recipient['address'], $recipient['name']);
            }
        }
        return $this;
    }
    protected function buildSubject($message)
    {
        if ($this->subject) {
            $message->subject($this->subject);
        } else {
            $message->subject(Str::title(Str::snake(class_basename($this), ' ')));
        }
        return $this;
    }
    protected function buildAttachments($message)
    {
        foreach ($this->attachments as $attachment) {
            $message->attach($attachment['file'], $attachment['options']);
        }
        foreach ($this->rawAttachments as $attachment) {
            $message->attachData(
                $attachment['data'], $attachment['name'], $attachment['options']
            );
        }
        $this->buildDiskAttachments($message);
        return $this;
    }
    protected function buildDiskAttachments($message)
    {
        foreach ($this->diskAttachments as $attachment) {
            $storage = Container::getInstance()->make(
                FilesystemFactory::class
            )->disk($attachment['disk']);
            $message->attachData(
                $storage->get($attachment['path']),
                $attachment['name'] ?? basename($attachment['path']),
                array_merge(['mime' => $storage->mimeType($attachment['path'])], $attachment['options'])
            );
        }
    }
    protected function runCallbacks($message)
    {
        foreach ($this->callbacks as $callback) {
            $callback($message->getSwiftMessage());
        }
        return $this;
    }
    public function locale($locale)
    {
        $this->locale = $locale;
        return $this;
    }
    public function priority($level = 3)
    {
        $this->callbacks[] = function ($message) use ($level) {
            $message->setPriority($level);
        };
        return $this;
    }
    public function from($address, $name = null)
    {
        return $this->setAddress($address, $name, 'from');
    }
    public function hasFrom($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'from');
    }
    public function to($address, $name = null)
    {
        return $this->setAddress($address, $name, 'to');
    }
    public function hasTo($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'to');
    }
    public function cc($address, $name = null)
    {
        return $this->setAddress($address, $name, 'cc');
    }
    public function hasCc($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'cc');
    }
    public function bcc($address, $name = null)
    {
        return $this->setAddress($address, $name, 'bcc');
    }
    public function hasBcc($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'bcc');
    }
    public function replyTo($address, $name = null)
    {
        return $this->setAddress($address, $name, 'replyTo');
    }
    public function hasReplyTo($address, $name = null)
    {
        return $this->hasRecipient($address, $name, 'replyTo');
    }
    protected function setAddress($address, $name = null, $property = 'to')
    {
        foreach ($this->addressesToArray($address, $name) as $recipient) {
            $recipient = $this->normalizeRecipient($recipient);
            $this->{$property}[] = [
                'name' => $recipient->name ?? null,
                'address' => $recipient->email,
            ];
        }
        return $this;
    }
    protected function addressesToArray($address, $name)
    {
        if (! is_array($address) && ! $address instanceof Collection) {
            $address = is_string($name) ? [['name' => $name, 'email' => $address]] : [$address];
        }
        return $address;
    }
    protected function normalizeRecipient($recipient)
    {
        if (is_array($recipient)) {
            return (object) $recipient;
        } elseif (is_string($recipient)) {
            return (object) ['email' => $recipient];
        }
        return $recipient;
    }
    protected function hasRecipient($address, $name = null, $property = 'to')
    {
        $expected = $this->normalizeRecipient(
            $this->addressesToArray($address, $name)[0]
        );
        $expected = [
            'name' => $expected->name ?? null,
            'address' => $expected->email,
        ];
        return collect($this->{$property})->contains(function ($actual) use ($expected) {
            if (! isset($expected['name'])) {
                return $actual['address'] == $expected['address'];
            }
            return $actual == $expected;
        });
    }
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    public function markdown($view, array $data = [])
    {
        $this->markdown = $view;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }
    public function view($view, array $data = [])
    {
        $this->view = $view;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }
    public function html($html)
    {
        $this->html = $html;
        return $this;
    }
    public function text($textView, array $data = [])
    {
        $this->textView = $textView;
        $this->viewData = array_merge($this->viewData, $data);
        return $this;
    }
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->viewData = array_merge($this->viewData, $key);
        } else {
            $this->viewData[$key] = $value;
        }
        return $this;
    }
    public function attach($file, array $options = [])
    {
        $this->attachments[] = compact('file', 'options');
        return $this;
    }
    public function attachFromStorage($path, $name = null, array $options = [])
    {
        return $this->attachFromStorageDisk(null, $path, $name, $options);
    }
    public function attachFromStorageDisk($disk, $path, $name = null, array $options = [])
    {
        $this->diskAttachments[] = [
            'disk' => $disk,
            'path' => $path,
            'name' => $name ?? basename($path),
            'options' => $options,
        ];
        return $this;
    }
    public function attachData($data, $name, array $options = [])
    {
        $this->rawAttachments[] = compact('data', 'name', 'options');
        return $this;
    }
    public function withSwiftMessage($callback)
    {
        $this->callbacks[] = $callback;
        return $this;
    }
    public static function buildViewDataUsing(callable $callback)
    {
        static::$viewDataCallback = $callback;
    }
    public function __call($method, $parameters)
    {
        if (Str::startsWith($method, 'with')) {
            return $this->with(Str::camel(substr($method, 4)), $parameters[0]);
        }
        static::throwBadMethodCallException($method);
    }
}
