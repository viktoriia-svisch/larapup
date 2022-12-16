<?php
namespace Illuminate\Mail;
use Parsedown;
use Illuminate\Support\HtmlString;
use Illuminate\Contracts\View\Factory as ViewFactory;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
class Markdown
{
    protected $view;
    protected $theme = 'default';
    protected $componentPaths = [];
    public function __construct(ViewFactory $view, array $options = [])
    {
        $this->view = $view;
        $this->theme = $options['theme'] ?? 'default';
        $this->loadComponentsFrom($options['paths'] ?? []);
    }
    public function render($view, array $data = [], $inliner = null)
    {
        $this->view->flushFinderCache();
        $contents = $this->view->replaceNamespace(
            'mail', $this->htmlComponentPaths()
        )->make($view, $data)->render();
        return new HtmlString(($inliner ?: new CssToInlineStyles)->convert(
            $contents, $this->view->make('mail::themes.'.$this->theme)->render()
        ));
    }
    public function renderText($view, array $data = [])
    {
        $this->view->flushFinderCache();
        $contents = $this->view->replaceNamespace(
            'mail', $this->markdownComponentPaths()
        )->make($view, $data)->render();
        return new HtmlString(
            html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", $contents), ENT_QUOTES, 'UTF-8')
        );
    }
    public static function parse($text)
    {
        $parsedown = new Parsedown;
        return new HtmlString($parsedown->text($text));
    }
    public function htmlComponentPaths()
    {
        return array_map(function ($path) {
            return $path.'/html';
        }, $this->componentPaths());
    }
    public function markdownComponentPaths()
    {
        return array_map(function ($path) {
            return $path.'/markdown';
        }, $this->componentPaths());
    }
    protected function componentPaths()
    {
        return array_unique(array_merge($this->componentPaths, [
            __DIR__.'/resources/views',
        ]));
    }
    public function loadComponentsFrom(array $paths = [])
    {
        $this->componentPaths = $paths;
    }
    public function theme($theme)
    {
        $this->theme = $theme;
        return $this;
    }
}
