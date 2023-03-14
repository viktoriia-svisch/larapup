<?php
namespace phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Types\Context as TypeContext;
class DescriptionFactory
{
    private $tagFactory;
    public function __construct(TagFactory $tagFactory)
    {
        $this->tagFactory = $tagFactory;
    }
    public function create($contents, TypeContext $context = null)
    {
        list($text, $tags) = $this->parse($this->lex($contents), $context);
        return new Description($text, $tags);
    }
    private function lex($contents)
    {
        $contents = $this->removeSuperfluousStartingWhitespace($contents);
        if (strpos($contents, '{@') === false) {
            return [$contents];
        }
        return preg_split(
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
                    )* # If there are more inline tags, match them as well. We use "*" since there may not be any
                )
            \}/Sux',
            $contents,
            null,
            PREG_SPLIT_DELIM_CAPTURE
        );
    }
    private function parse($tokens, TypeContext $context)
    {
        $count = count($tokens);
        $tagCount = 0;
        $tags  = [];
        for ($i = 1; $i < $count; $i += 2) {
            $tags[] = $this->tagFactory->create($tokens[$i], $context);
            $tokens[$i] = '%' . ++$tagCount . '$s';
        }
        for ($i = 0; $i < $count; $i += 2) {
            $tokens[$i] = str_replace(['{@}', '{}', '%'], ['@', '}', '%%'], $tokens[$i]);
        }
        return [implode('', $tokens), $tags];
    }
    private function removeSuperfluousStartingWhitespace($contents)
    {
        $lines = explode("\n", $contents);
        if (count($lines) <= 1) {
            return $contents;
        }
        $startingSpaceCount = 9999999;
        for ($i = 1; $i < count($lines); $i++) {
            if (strlen(trim($lines[$i])) === 0) {
                continue;
            }
            $startingSpaceCount = min($startingSpaceCount, strlen($lines[$i]) - strlen(ltrim($lines[$i])));
        }
        if ($startingSpaceCount > 0) {
            for ($i = 1; $i < count($lines); $i++) {
                $lines[$i] = substr($lines[$i], $startingSpaceCount);
            }
        }
        return implode("\n", $lines);
    }
}
