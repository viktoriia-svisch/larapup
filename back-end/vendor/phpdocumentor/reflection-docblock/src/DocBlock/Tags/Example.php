<?php
namespace phpDocumentor\Reflection\DocBlock\Tags;
use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use Webmozart\Assert\Assert;
final class Example extends BaseTag
{
    private $filePath;
    private $isURI = false;
    private $startingLine;
    private $lineCount;
    public function __construct($filePath, $isURI, $startingLine, $lineCount, $description)
    {
        Assert::notEmpty($filePath);
        Assert::integer($startingLine);
        Assert::greaterThanEq($startingLine, 0);
        $this->filePath = $filePath;
        $this->startingLine = $startingLine;
        $this->lineCount = $lineCount;
        $this->name = 'example';
        if ($description !== null) {
            $this->description = trim($description);
        }
        $this->isURI = $isURI;
    }
    public function getContent()
    {
        if (null === $this->description) {
            $filePath = '"' . $this->filePath . '"';
            if ($this->isURI) {
                $filePath = $this->isUriRelative($this->filePath)
                    ? str_replace('%2F', '/', rawurlencode($this->filePath))
                    :$this->filePath;
            }
            return trim($filePath . ' ' . parent::getDescription());
        }
        return $this->description;
    }
    public static function create($body)
    {
        if (! preg_match('/^(?:\"([^\"]+)\"|(\S+))(?:\s+(.*))?$/sux', $body, $matches)) {
            return null;
        }
        $filePath = null;
        $fileUri  = null;
        if ('' !== $matches[1]) {
            $filePath = $matches[1];
        } else {
            $fileUri = $matches[2];
        }
        $startingLine = 1;
        $lineCount    = null;
        $description  = null;
        if (array_key_exists(3, $matches)) {
            $description = $matches[3];
            if (preg_match('/^([1-9]\d*)(?:\s+((?1))\s*)?(.*)$/sux', $matches[3], $contentMatches)) {
                $startingLine = (int)$contentMatches[1];
                if (isset($contentMatches[2]) && $contentMatches[2] !== '') {
                    $lineCount = (int)$contentMatches[2];
                }
                if (array_key_exists(3, $contentMatches)) {
                    $description = $contentMatches[3];
                }
            }
        }
        return new static(
            $filePath !== null?$filePath:$fileUri,
            $fileUri !== null,
            $startingLine,
            $lineCount,
            $description
        );
    }
    public function getFilePath()
    {
        return $this->filePath;
    }
    public function __toString()
    {
        return $this->filePath . ($this->description ? ' ' . $this->description : '');
    }
    private function isUriRelative($uri)
    {
        return false === strpos($uri, ':');
    }
    public function getStartingLine()
    {
        return $this->startingLine;
    }
    public function getLineCount()
    {
        return $this->lineCount;
    }
}
