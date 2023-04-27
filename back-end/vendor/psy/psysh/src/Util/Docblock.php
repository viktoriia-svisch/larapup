<?php
namespace Psy\Util;
class Docblock
{
    public static $vectors = [
        'throws' => ['type', 'desc'],
        'param'  => ['type', 'var', 'desc'],
        'return' => ['type', 'desc'],
    ];
    protected $reflector;
    public $desc;
    public $tags;
    public $comment;
    public function __construct(\Reflector $reflector)
    {
        $this->reflector = $reflector;
        $this->setComment($reflector->getDocComment());
    }
    protected function setComment($comment)
    {
        $this->desc    = '';
        $this->tags    = [];
        $this->comment = $comment;
        $this->parseComment($comment);
    }
    protected static function prefixLength(array $lines)
    {
        $lines = \array_filter($lines, function ($line) {
            return \substr($line, \strspn($line, "* \t\n\r\0\x0B"));
        });
        \sort($lines);
        $first = \reset($lines);
        $last  = \end($lines);
        $count = \min(\strlen($first), \strlen($last));
        for ($i = 0; $i < $count; $i++) {
            if ($first[$i] !== $last[$i]) {
                return $i;
            }
        }
        return $count;
    }
    protected function parseComment($comment)
    {
        $comment = \substr($comment, 3, -2);
        $comment = \array_filter(\preg_split('/\r?\n\r?/', $comment));
        $prefixLength = self::prefixLength($comment);
        $comment = \array_map(function ($line) use ($prefixLength) {
            return \rtrim(\substr($line, $prefixLength));
        }, $comment);
        $blocks = [];
        $b = -1;
        foreach ($comment as $line) {
            if (self::isTagged($line)) {
                $b++;
                $blocks[] = [];
            } elseif ($b === -1) {
                $b = 0;
                $blocks[] = [];
            }
            $blocks[$b][] = $line;
        }
        foreach ($blocks as $block => $body) {
            $body = \trim(\implode("\n", $body));
            if ($block === 0 && !self::isTagged($body)) {
                $this->desc = $body;
            } else {
                $tag  = \substr(self::strTag($body), 1);
                $body = \ltrim(\substr($body, \strlen($tag) + 2));
                if (isset(self::$vectors[$tag])) {
                    $count = \count(self::$vectors[$tag]);
                    if ($body) {
                        $parts = \preg_split('/\s+/', $body, $count);
                    } else {
                        $parts = [];
                    }
                    $parts = \array_pad($parts, $count, null);
                    $this->tags[$tag][] = \array_combine(self::$vectors[$tag], $parts);
                } else {
                    $this->tags[$tag][] = $body;
                }
            }
        }
    }
    public function hasTag($tag)
    {
        return \is_array($this->tags) && \array_key_exists($tag, $this->tags);
    }
    public function tag($tag)
    {
        return $this->hasTag($tag) ? $this->tags[$tag] : null;
    }
    public static function isTagged($str)
    {
        return isset($str[1]) && $str[0] === '@' && !\preg_match('/[^A-Za-z]/', $str[1]);
    }
    public static function strTag($str)
    {
        if (\preg_match('/^@[a-z0-9_]+/', $str, $matches)) {
            return $matches[0];
        }
    }
}
