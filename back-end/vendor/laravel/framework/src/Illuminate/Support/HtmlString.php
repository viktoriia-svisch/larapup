<?php
namespace Illuminate\Support;
use Illuminate\Contracts\Support\Htmlable;
class HtmlString implements Htmlable
{
    protected $html;
    public function __construct($html)
    {
        $this->html = $html;
    }
    public function toHtml()
    {
        return $this->html;
    }
    public function __toString()
    {
        return $this->toHtml();
    }
}
