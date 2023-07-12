<?php
namespace phpDocumentor\Reflection\DocBlock\Tags\Formatter;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter;
class AlignFormatter implements Formatter
{
    protected $maxLen = 0;
    public function __construct(array $tags)
    {
        foreach ($tags as $tag) {
            $this->maxLen = max($this->maxLen, strlen($tag->getName()));
        }
    }
    public function format(Tag $tag)
    {
        return '@' . $tag->getName() . str_repeat(' ', $this->maxLen - strlen($tag->getName()) + 1) . (string)$tag;
    }
}
