<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag;
class VersionTag extends Tag
{
    const REGEX_VECTOR = '(?:
        \d\S*
        |
        [^\s\:]+\:\s*\$[^\$]+\$
    )';
    protected $version = '';
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = "{$this->version} {$this->description}";
        }
        return $this->content;
    }
    public function setContent($content)
    {
        parent::setContent($content);
        if (preg_match(
            '/^
                (' . self::REGEX_VECTOR . ')
                \s*
                (.+)?
            $/sux',
            $this->description,
            $matches
        )) {
            $this->version = $matches[1];
            $this->setDescription(isset($matches[2]) ? $matches[2] : '');
            $this->content = $content;
        }
        return $this;
    }
    public function getVersion()
    {
        return $this->version;
    }
    public function setVersion($version)
    {
        $this->version
            = preg_match('/^' . self::REGEX_VECTOR . '$/ux', $version)
            ? $version
            : '';
        $this->content = null;
        return $this;
    }
}
