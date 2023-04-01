<?php
namespace Illuminate\Notifications\Messages;
use Illuminate\Notifications\Action;
use Illuminate\Contracts\Support\Htmlable;
class SimpleMessage
{
    public $level = 'info';
    public $subject;
    public $greeting;
    public $salutation;
    public $introLines = [];
    public $outroLines = [];
    public $actionText;
    public $actionUrl;
    public function success()
    {
        $this->level = 'success';
        return $this;
    }
    public function error()
    {
        $this->level = 'error';
        return $this;
    }
    public function level($level)
    {
        $this->level = $level;
        return $this;
    }
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }
    public function greeting($greeting)
    {
        $this->greeting = $greeting;
        return $this;
    }
    public function salutation($salutation)
    {
        $this->salutation = $salutation;
        return $this;
    }
    public function line($line)
    {
        return $this->with($line);
    }
    public function with($line)
    {
        if ($line instanceof Action) {
            $this->action($line->text, $line->url);
        } elseif (! $this->actionText) {
            $this->introLines[] = $this->formatLine($line);
        } else {
            $this->outroLines[] = $this->formatLine($line);
        }
        return $this;
    }
    protected function formatLine($line)
    {
        if ($line instanceof Htmlable) {
            return $line;
        }
        if (is_array($line)) {
            return implode(' ', array_map('trim', $line));
        }
        return trim(implode(' ', array_map('trim', preg_split('/\\r\\n|\\r|\\n/', $line))));
    }
    public function action($text, $url)
    {
        $this->actionText = $text;
        $this->actionUrl = $url;
        return $this;
    }
    public function toArray()
    {
        return [
            'level' => $this->level,
            'subject' => $this->subject,
            'greeting' => $this->greeting,
            'salutation' => $this->salutation,
            'introLines' => $this->introLines,
            'outroLines' => $this->outroLines,
            'actionText' => $this->actionText,
            'actionUrl' => $this->actionUrl,
        ];
    }
}
