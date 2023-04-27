<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag;
class ParamTag extends ReturnTag
{
    protected $variableName = '';
    protected $isVariadic = false;
    public function getContent()
    {
        if (null === $this->content) {
            $this->content
                = "{$this->type} {$this->variableName} {$this->description}";
        }
        return $this->content;
    }
    public function setContent($content)
    {
        Tag::setContent($content);
        $parts = preg_split(
            '/(\s+)/Su',
            $this->description,
            3,
            PREG_SPLIT_DELIM_CAPTURE
        );
        if (isset($parts[0])
            && (strlen($parts[0]) > 0)
            && ($parts[0][0] !== '$')
        ) {
            $this->type = array_shift($parts);
            array_shift($parts);
        }
        if (isset($parts[0])
            && (strlen($parts[0]) > 0)
            && ($parts[0][0] == '$' || substr($parts[0], 0, 4) === '...$')
        ) {
            $this->variableName = array_shift($parts);
            array_shift($parts);
            if (substr($this->variableName, 0, 3) === '...') {
                $this->isVariadic = true;
                $this->variableName = substr($this->variableName, 3);
            }
        }
        $this->setDescription(implode('', $parts));
        $this->content = $content;
        return $this;
    }
    public function getVariableName()
    {
        return $this->variableName;
    }
    public function setVariableName($name)
    {
        $this->variableName = $name;
        $this->content = null;
        return $this;
    }
    public function isVariadic()
    {
        return $this->isVariadic;
    }
}
