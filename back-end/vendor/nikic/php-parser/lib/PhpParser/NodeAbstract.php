<?php declare(strict_types=1);
namespace PhpParser;
abstract class NodeAbstract implements Node, \JsonSerializable
{
    protected $attributes;
    public function __construct(array $attributes = []) {
        $this->attributes = $attributes;
    }
    public function getLine() : int {
        return $this->attributes['startLine'] ?? -1;
    }
    public function getStartLine() : int {
        return $this->attributes['startLine'] ?? -1;
    }
    public function getEndLine() : int {
        return $this->attributes['endLine'] ?? -1;
    }
    public function getStartTokenPos() : int {
        return $this->attributes['startTokenPos'] ?? -1;
    }
    public function getEndTokenPos() : int {
        return $this->attributes['endTokenPos'] ?? -1;
    }
    public function getStartFilePos() : int {
        return $this->attributes['startFilePos'] ?? -1;
    }
    public function getEndFilePos() : int {
        return $this->attributes['endFilePos'] ?? -1;
    }
    public function getComments() : array {
        return $this->attributes['comments'] ?? [];
    }
    public function getDocComment() {
        $comments = $this->getComments();
        if (!$comments) {
            return null;
        }
        $lastComment = $comments[count($comments) - 1];
        if (!$lastComment instanceof Comment\Doc) {
            return null;
        }
        return $lastComment;
    }
    public function setDocComment(Comment\Doc $docComment) {
        $comments = $this->getComments();
        $numComments = count($comments);
        if ($numComments > 0 && $comments[$numComments - 1] instanceof Comment\Doc) {
            $comments[$numComments - 1] = $docComment;
        } else {
            $comments[] = $docComment;
        }
        $this->setAttribute('comments', $comments);
    }
    public function setAttribute(string $key, $value) {
        $this->attributes[$key] = $value;
    }
    public function hasAttribute(string $key) : bool {
        return array_key_exists($key, $this->attributes);
    }
    public function getAttribute(string $key, $default = null) {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        return $default;
    }
    public function getAttributes() : array {
        return $this->attributes;
    }
    public function setAttributes(array $attributes) {
        $this->attributes = $attributes;
    }
    public function jsonSerialize() : array {
        return ['nodeType' => $this->getType()] + get_object_vars($this);
    }
}
