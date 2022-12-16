<?php
namespace Illuminate\View\Compilers\Concerns;
trait CompilesEchos
{
    protected function compileEchos($value)
    {
        foreach ($this->getEchoMethods() as $method) {
            $value = $this->$method($value);
        }
        return $value;
    }
    protected function getEchoMethods()
    {
        return [
            'compileRawEchos',
            'compileEscapedEchos',
            'compileRegularEchos',
        ];
    }
    protected function compileRawEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->rawTags[0], $this->rawTags[1]);
        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$matches[2]}; ?>{$whitespace}";
        };
        return preg_replace_callback($pattern, $callback, $value);
    }
    protected function compileRegularEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);
        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            $wrapped = sprintf($this->echoFormat, $matches[2]);
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$wrapped}; ?>{$whitespace}";
        };
        return preg_replace_callback($pattern, $callback, $value);
    }
    protected function compileEscapedEchos($value)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);
        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3].$matches[3];
            return $matches[1] ? $matches[0] : "<?php echo e({$matches[2]}); ?>{$whitespace}";
        };
        return preg_replace_callback($pattern, $callback, $value);
    }
}
