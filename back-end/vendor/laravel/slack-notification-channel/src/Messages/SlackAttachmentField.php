<?php
namespace Illuminate\Notifications\Messages;
class SlackAttachmentField
{
    protected $title;
    protected $content;
    protected $short = true;
    public function title($title)
    {
        $this->title = $title;
        return $this;
    }
    public function content($content)
    {
        $this->content = $content;
        return $this;
    }
    public function long()
    {
        $this->short = false;
        return $this;
    }
    public function toArray()
    {
        return [
            'title' => $this->title,
            'value' => $this->content,
            'short' => $this->short,
        ];
    }
}
