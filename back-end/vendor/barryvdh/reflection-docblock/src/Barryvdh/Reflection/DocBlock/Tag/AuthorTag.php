<?php
namespace Barryvdh\Reflection\DocBlock\Tag;
use Barryvdh\Reflection\DocBlock\Tag;
class AuthorTag extends Tag
{
    const REGEX_AUTHOR_NAME = '[^\<]*';
    const REGEX_AUTHOR_EMAIL = '[^\>]*';
    protected $authorName = '';
    protected $authorEmail = '';
    public function getContent()
    {
        if (null === $this->content) {
            $this->content = $this->authorName;
            if ('' != $this->authorEmail) {
                $this->content .= "<{$this->authorEmail}>";
            }
        }
        return $this->content;
    }
    public function setContent($content)
    {
        parent::setContent($content);
        if (preg_match(
            '/^(' . self::REGEX_AUTHOR_NAME .
            ')(\<(' . self::REGEX_AUTHOR_EMAIL .
            ')\>)?$/u',
            $this->description,
            $matches
        )) {
            $this->authorName = trim($matches[1]);
            if (isset($matches[3])) {
                $this->authorEmail = trim($matches[3]);
            }
        }
        return $this;
    }
    public function getAuthorName()
    {
        return $this->authorName;
    }
    public function setAuthorName($authorName)
    {
        $this->content = null;
        $this->authorName
            = preg_match('/^' . self::REGEX_AUTHOR_NAME . '$/u', $authorName)
            ? $authorName : '';
        return $this;
    }
    public function getAuthorEmail()
    {
        return $this->authorEmail;
    }
    public function setAuthorEmail($authorEmail)
    {
        $this->authorEmail
            = preg_match('/^' . self::REGEX_AUTHOR_EMAIL . '$/u', $authorEmail)
            ? $authorEmail : '';
        $this->content = null;
        return $this;
    }
}
