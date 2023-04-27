<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Type\Collection;
class ReturnTag extends Tag
{
    protected $type = '';
    protected $types = null;
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = "{$this->getType()} {$this->description}";
        }
        return $this->content;
    }
    public function setContent($content)
    {
        parent::setContent($content);
        $parts = preg_split('/\s+/Su', $this->description, 2);
        $this->type = $parts[0];
        $this->types = null;
        $this->setDescription(isset($parts[1]) ? $parts[1] : '');
        $this->content = $content;
        return $this;
    }
    public function getTypes()
    {
        return $this->getTypesCollection()->getArrayCopy();
    }
    public function getType()
    {
        return (string) $this->getTypesCollection();
    }
    public function setType($type)
    {
        $this->type = $type;
        $this->types = null;
        $this->content = null;
        return $this;
    }
    public function addType($type)
    {
        $this->type = $this->type . Collection::OPERATOR_OR . $type;
        $this->types = null;
        $this->content = null;
        return $this;
    }
    protected function getTypesCollection()
    {
        if (null === $this->types) {
            $this->types = new Collection(
                array($this->type),
                $this->docblock ? $this->docblock->getContext() : null
            );
        }
        return $this->types;
    }
}
