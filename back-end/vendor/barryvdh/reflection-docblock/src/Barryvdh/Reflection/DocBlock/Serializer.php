<?php
namespace Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock;
class Serializer
{
    protected $indentString = ' ';
    protected $indent = 0;
    protected $isFirstLineIndented = true;
    protected $lineLength = null;
    public function __construct(
        $indent = 0,
        $indentString = ' ',
        $indentFirstLine = true,
        $lineLength = null
    ) {
        $this->setIndentationString($indentString);
        $this->setIndent($indent);
        $this->setIsFirstLineIndented($indentFirstLine);
        $this->setLineLength($lineLength);
    }
    public function setIndentationString($indentString)
    {
        $this->indentString = (string)$indentString;
        return $this;
    }
    public function getIndentationString()
    {
        return $this->indentString;
    }
    public function setIndent($indent)
    {
        $this->indent = (int)$indent;
        return $this;
    }
    public function getIndent()
    {
        return $this->indent;
    }
    public function setIsFirstLineIndented($indentFirstLine)
    {
        $this->isFirstLineIndented = (bool)$indentFirstLine;
        return $this;
    }
    public function isFirstLineIndented()
    {
        return $this->isFirstLineIndented;
    }
    public function setLineLength($lineLength)
    {
        $this->lineLength = null === $lineLength ? null : (int)$lineLength;
        return $this;
    }
    public function getLineLength()
    {
        return $this->lineLength;
    }
    public function getDocComment(DocBlock $docblock)
    {
        $indent = str_repeat($this->indentString, $this->indent);
        $firstIndent = $this->isFirstLineIndented ? $indent : '';
        $text = $docblock->getText();
        if ($this->lineLength) {
            $wrapLength = $this->lineLength - strlen($indent) - 3;
            $text = wordwrap($text, $wrapLength);
        }
        $text = str_replace("\n", "\n{$indent} * ", $text);
        $comment = "{$firstIndent}
        foreach ($docblock->getTags() as $tag) {
            $tagText = (string) $tag;
            if ($this->lineLength) {
                $tagText = wordwrap($tagText, $wrapLength);
            }
            $tagText = str_replace("\n", "\n{$indent} * ", $tagText);
            $comment .= "{$indent} * {$tagText}\n";
        }
        $comment .= $indent . ' */';
        return $comment;
    }
}
