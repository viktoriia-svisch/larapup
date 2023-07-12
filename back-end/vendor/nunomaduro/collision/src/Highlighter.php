<?php
namespace NunoMaduro\Collision;
use JakubOnderka\PhpConsoleColor\ConsoleColor;
use JakubOnderka\PhpConsoleHighlighter\Highlighter as BaseHighlighter;
use NunoMaduro\Collision\Contracts\Highlighter as HighlighterContract;
class Highlighter extends BaseHighlighter implements HighlighterContract
{
    protected $theme = [
        BaseHighlighter::TOKEN_STRING => ['light_gray'],
        BaseHighlighter::TOKEN_COMMENT => ['dark_gray', 'italic'],
        BaseHighlighter::TOKEN_KEYWORD => ['yellow'],
        BaseHighlighter::TOKEN_DEFAULT => ['default', 'bold'],
        BaseHighlighter::TOKEN_HTML => ['blue', 'bold'],
        BaseHighlighter::ACTUAL_LINE_MARK => ['bg_red', 'bold'],
        BaseHighlighter::LINE_NUMBER => ['dark_gray', 'italic'],
    ];
    public function __construct(ConsoleColor $color = null)
    {
        parent::__construct($color = $color ?: new ConsoleColor);
        foreach ($this->theme as $name => $styles) {
            $color->addTheme((string) $name, $styles);
        }
    }
    public function highlight(string $content, int $line): string
    {
        return rtrim($this->getCodeSnippet($content, $line, 4, 4));
    }
}
