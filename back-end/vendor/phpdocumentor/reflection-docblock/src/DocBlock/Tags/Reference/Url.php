<?php
namespace phpDocumentor\Reflection\DocBlock\Tags\Reference;
use Webmozart\Assert\Assert;
final class Url implements Reference
{
    private $uri;
    public function __construct($uri)
    {
        Assert::stringNotEmpty($uri);
        $this->uri = $uri;
    }
    public function __toString()
    {
        return $this->uri;
    }
}
