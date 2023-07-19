<?php
namespace Symfony\Component\Console\Formatter;
use Symfony\Component\Console\Exception\InvalidArgumentException;
class OutputFormatter implements WrappableOutputFormatterInterface
{
    private $decorated;
    private $styles = [];
    private $styleStack;
    public static function escape($text)
    {
        $text = preg_replace('/([^\\\\]?)</', '$1\\<', $text);
        return self::escapeTrailingBackslash($text);
    }
    public static function escapeTrailingBackslash($text)
    {
        if ('\\' === substr($text, -1)) {
            $len = \strlen($text);
            $text = rtrim($text, '\\');
            $text = str_replace("\0", '', $text);
            $text .= str_repeat("\0", $len - \strlen($text));
        }
        return $text;
    }
    public function __construct(bool $decorated = false, array $styles = [])
    {
        $this->decorated = $decorated;
        $this->setStyle('error', new OutputFormatterStyle('white', 'red'));
        $this->setStyle('info', new OutputFormatterStyle('green'));
        $this->setStyle('comment', new OutputFormatterStyle('yellow'));
        $this->setStyle('question', new OutputFormatterStyle('black', 'cyan'));
        foreach ($styles as $name => $style) {
            $this->setStyle($name, $style);
        }
        $this->styleStack = new OutputFormatterStyleStack();
    }
    public function setDecorated($decorated)
    {
        $this->decorated = (bool) $decorated;
    }
    public function isDecorated()
    {
        return $this->decorated;
    }
    public function setStyle($name, OutputFormatterStyleInterface $style)
    {
        $this->styles[strtolower($name)] = $style;
    }
    public function hasStyle($name)
    {
        return isset($this->styles[strtolower($name)]);
    }
    public function getStyle($name)
    {
        if (!$this->hasStyle($name)) {
            throw new InvalidArgumentException(sprintf('Undefined style: %s', $name));
        }
        return $this->styles[strtolower($name)];
    }
    public function format($message)
    {
        return $this->formatAndWrap((string) $message, 0);
    }
    public function formatAndWrap(string $message, int $width)
    {
        $offset = 0;
        $output = '';
        $tagRegex = '[a-z][a-z0-9,_=;-]*+';
        $currentLineLength = 0;
        preg_match_all("#<(($tagRegex) | /($tagRegex)?)>#ix", $message, $matches, PREG_OFFSET_CAPTURE);
        foreach ($matches[0] as $i => $match) {
            $pos = $match[1];
            $text = $match[0];
            if (0 != $pos && '\\' == $message[$pos - 1]) {
                continue;
            }
            $output .= $this->applyCurrentStyle(substr($message, $offset, $pos - $offset), $output, $width, $currentLineLength);
            $offset = $pos + \strlen($text);
            if ($open = '/' != $text[1]) {
                $tag = $matches[1][$i][0];
            } else {
                $tag = isset($matches[3][$i][0]) ? $matches[3][$i][0] : '';
            }
            if (!$open && !$tag) {
                $this->styleStack->pop();
            } elseif (false === $style = $this->createStyleFromString($tag)) {
                $output .= $this->applyCurrentStyle($text, $output, $width, $currentLineLength);
            } elseif ($open) {
                $this->styleStack->push($style);
            } else {
                $this->styleStack->pop($style);
            }
        }
        $output .= $this->applyCurrentStyle(substr($message, $offset), $output, $width, $currentLineLength);
        if (false !== strpos($output, "\0")) {
            return strtr($output, ["\0" => '\\', '\\<' => '<']);
        }
        return str_replace('\\<', '<', $output);
    }
    public function getStyleStack()
    {
        return $this->styleStack;
    }
    private function createStyleFromString(string $string)
    {
        if (isset($this->styles[$string])) {
            return $this->styles[$string];
        }
        if (!preg_match_all('/([^=]+)=([^;]+)(;|$)/', $string, $matches, PREG_SET_ORDER)) {
            return false;
        }
        $style = new OutputFormatterStyle();
        foreach ($matches as $match) {
            array_shift($match);
            $match[0] = strtolower($match[0]);
            if ('fg' == $match[0]) {
                $style->setForeground(strtolower($match[1]));
            } elseif ('bg' == $match[0]) {
                $style->setBackground(strtolower($match[1]));
            } elseif ('options' === $match[0]) {
                preg_match_all('([^,;]+)', strtolower($match[1]), $options);
                $options = array_shift($options);
                foreach ($options as $option) {
                    $style->setOption($option);
                }
            } else {
                return false;
            }
        }
        return $style;
    }
    private function applyCurrentStyle(string $text, string $current, int $width, int &$currentLineLength): string
    {
        if ('' === $text) {
            return '';
        }
        if (!$width) {
            return $this->isDecorated() ? $this->styleStack->getCurrent()->apply($text) : $text;
        }
        if (!$currentLineLength && '' !== $current) {
            $text = ltrim($text);
        }
        if ($currentLineLength) {
            $prefix = substr($text, 0, $i = $width - $currentLineLength)."\n";
            $text = substr($text, $i);
        } else {
            $prefix = '';
        }
        preg_match('~(\\n)$~', $text, $matches);
        $text = $prefix.preg_replace('~([^\\n]{'.$width.'})\\ *~', "\$1\n", $text);
        $text = rtrim($text, "\n").($matches[1] ?? '');
        if (!$currentLineLength && '' !== $current && "\n" !== substr($current, -1)) {
            $text = "\n".$text;
        }
        $lines = explode("\n", $text);
        if ($width === $currentLineLength = \strlen(end($lines))) {
            $currentLineLength = 0;
        }
        if ($this->isDecorated()) {
            foreach ($lines as $i => $line) {
                $lines[$i] = $this->styleStack->getCurrent()->apply($line);
            }
        }
        return implode("\n", $lines);
    }
}