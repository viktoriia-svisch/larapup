<?php
namespace Illuminate\Notifications\Messages;
use Closure;
class SlackMessage
{
    public $level = 'info';
    public $username;
    public $icon;
    public $image;
    public $channel;
    public $content;
    public $linkNames = 0;
    public $unfurlLinks;
    public $unfurlMedia;
    public $attachments = [];
    public $http = [];
    public function info()
    {
        $this->level = 'info';
        return $this;
    }
    public function success()
    {
        $this->level = 'success';
        return $this;
    }
    public function warning()
    {
        $this->level = 'warning';
        return $this;
    }
    public function error()
    {
        $this->level = 'error';
        return $this;
    }
    public function from($username, $icon = null)
    {
        $this->username = $username;
        if (! is_null($icon)) {
            $this->icon = $icon;
        }
        return $this;
    }
    public function image($image)
    {
        $this->image = $image;
        return $this;
    }
    public function to($channel)
    {
        $this->channel = $channel;
        return $this;
    }
    public function content($content)
    {
        $this->content = $content;
        return $this;
    }
    public function attachment(Closure $callback)
    {
        $this->attachments[] = $attachment = new SlackAttachment;
        $callback($attachment);
        return $this;
    }
    public function color()
    {
        switch ($this->level) {
            case 'success':
                return 'good';
            case 'error':
                return 'danger';
            case 'warning':
                return 'warning';
        }
    }
    public function linkNames()
    {
        $this->linkNames = 1;
        return $this;
    }
    public function unfurlLinks($unfurl)
    {
        $this->unfurlLinks = $unfurl;
        return $this;
    }
    public function unfurlMedia($unfurl)
    {
        $this->unfurlMedia = $unfurl;
        return $this;
    }
    public function http(array $options)
    {
        $this->http = $options;
        return $this;
    }
}
