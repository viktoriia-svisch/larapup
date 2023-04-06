<?php
namespace Barryvdh\Reflection\DocBlock;
use Barryvdh\Reflection\DocBlock;
class Description implements \Reflector
{
    protected $contents = '';
    protected $parsedContents = null;
    protected $docblock = null;
    public function __construct($content, DocBlock $docblock = null)
    {
        $this->setContent($content)->setDocBlock($docblock);
    }
    public function getContents()
    {
        return $this->contents;
    }
    public function setContent($content)
    {
        $this->contents = trim($content);
        $this->parsedContents = null;
        return $this;
    }
    public function getParsedContents()
    {
        if (null === $this->parsedContents) {
            $this->parsedContents = preg_split(
                '/\{
                    (?!@\})
                    (\@
                        [^{}]*
                        (?:
                            (?:
                                \{(?1)?\}
                                |
                                \{
                            )
                            [^{}]*
                        )* # If there are more inline tags, match them as well.
                    )
                \}/Sux',
                $this->contents,
                null,
                PREG_SPLIT_DELIM_CAPTURE
            );
            $count = count($this->parsedContents);
            for ($i=1; $i<$count; $i += 2) {
                $this->parsedContents[$i] = Tag::createInstance(
                    $this->parsedContents[$i],
                    $this->docblock
                );
            }
            for ($i=0; $i<$count; $i += 2) {
                $this->parsedContents[$i] = str_replace(
                    array('{@}', '{}'),
                    array('@', '}'),
                    $this->parsedContents[$i]
                );
            }
        }
        return $this->parsedContents;
    }
    public function getFormattedContents()
    {
        $result = $this->contents;
        if (strpos($result, '<code>') !== false) {
            $result = str_replace(
                array('<code>', "<code>\r\n", "<code>\n", "<code>\r", '</code>'),
                array('<pre><code>', '<code>', '<code>', '<code>', '</code></pre>'),
                $result
            );
        }
        if (class_exists('Parsedown')) {
            $markdown = \Parsedown::instance();
            $result = $markdown->parse($result);
        } elseif (class_exists('dflydev\markdown\MarkdownExtraParser')) {
            $markdown = new \dflydev\markdown\MarkdownExtraParser();
            $result = $markdown->transformMarkdown($result);
        }
        return trim($result);
    }
    public function getDocBlock()
    {
        return $this->docblock;
    }
    public function setDocBlock(DocBlock $docblock = null)
    {
        $this->docblock = $docblock;
        return $this;
    }
    public static function export()
    {
        throw new \Exception('Not yet implemented');
    }
    public function __toString()
    {
        return $this->getContents();
    }
}
