<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag;
class LinkTag extends Tag
{
    protected $link = '';
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = "{$this->link} {$this->description}";
        }
        return $this->content;
    }
    public function setContent($content)
    {
        parent::setContent($content);
        $parts = preg_split('/\s+/Su', $this->description, 2);
        $this->link = $parts[0];
        $this->setDescription(isset($parts[1]) ? $parts[1] : $parts[0]);
        $this->content = $content;
        return $this;
    }
    public function getLink()
    {
        return $this->link;
    }
    public function setLink($link)
    {
        $this->link = $link;
        $this->content = null;
        return $this;
    }
}
