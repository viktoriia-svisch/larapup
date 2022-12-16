<?php
namespace Illuminate\Notifications\Messages;
use Illuminate\Support\InteractsWithTime;
class SlackAttachment
{
    use InteractsWithTime;
    public $title;
    public $url;
    public $pretext;
    public $content;
    public $fallback;
    public $color;
    public $fields;
    public $markdown;
    public $imageUrl;
    public $thumbUrl;
    public $actions = [];
    public $authorName;
    public $authorLink;
    public $authorIcon;
    public $footer;
    public $footerIcon;
    public $timestamp;
    public function title($title, $url = null)
    {
        $this->title = $title;
        $this->url = $url;
        return $this;
    }
    public function pretext($pretext)
    {
        $this->pretext = $pretext;
        return $this;
    }
    public function content($content)
    {
        $this->content = $content;
        return $this;
    }
    public function fallback($fallback)
    {
        $this->fallback = $fallback;
        return $this;
    }
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }
    public function field($title, $content = '')
    {
        if (is_callable($title)) {
            $callback = $title;
            $callback($attachmentField = new SlackAttachmentField);
            $this->fields[] = $attachmentField;
            return $this;
        }
        $this->fields[$title] = $content;
        return $this;
    }
    public function fields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }
    public function markdown(array $fields)
    {
        $this->markdown = $fields;
        return $this;
    }
    public function image($url)
    {
        $this->imageUrl = $url;
        return $this;
    }
    public function thumb($url)
    {
        $this->thumbUrl = $url;
        return $this;
    }
    public function action($title, $url, $style = '')
    {
        $this->actions[] = [
            'type' => 'button',
            'text' => $title,
            'url' => $url,
            'style' => $style,
        ];
        return $this;
    }
    public function author($name, $link = null, $icon = null)
    {
        $this->authorName = $name;
        $this->authorLink = $link;
        $this->authorIcon = $icon;
        return $this;
    }
    public function footer($footer)
    {
        $this->footer = $footer;
        return $this;
    }
    public function footerIcon($icon)
    {
        $this->footerIcon = $icon;
        return $this;
    }
    public function timestamp($timestamp)
    {
        $this->timestamp = $this->availableAt($timestamp);
        return $this;
    }
}
