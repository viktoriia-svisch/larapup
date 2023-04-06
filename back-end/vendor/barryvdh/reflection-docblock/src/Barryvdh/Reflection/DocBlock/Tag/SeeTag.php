<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag;
class SeeTag extends Tag
{
    protected $refers = null;
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = "{$this->refers} {$this->description}";
        }
        return $this->content;
    }
    public function setContent($content)
    {
        parent::setContent($content);
        $parts = preg_split('/\s+/Su', $this->description, 2);
        $this->refers = $parts[0];
        $this->setDescription(isset($parts[1]) ? $parts[1] : '');
        $this->content = $content;
        return $this;
    }
    public function getReference()
    {
        return $this->refers;
    }
    public function setReference($refers)
    {
        $this->refers = $refers;
        $this->content = null;
        return $this;
    }
}
