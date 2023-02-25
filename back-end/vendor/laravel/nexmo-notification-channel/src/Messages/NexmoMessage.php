<?php
namespace Illuminate\Notifications\Messages;
class NexmoMessage
{
    public $content;
    public $from;
    public $type = 'text';
    public function __construct($content = '')
    {
        $this->content = $content;
    }
    public function content($content)
    {
        $this->content = $content;
        return $this;
    }
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }
    public function unicode()
    {
        $this->type = 'unicode';
        return $this;
    }
}
