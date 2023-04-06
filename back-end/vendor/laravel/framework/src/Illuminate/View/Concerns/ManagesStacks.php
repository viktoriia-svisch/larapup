<?php
namespace Illuminate\View\Concerns;
use InvalidArgumentException;
trait ManagesStacks
{
    protected $pushes = [];
    protected $prepends = [];
    protected $pushStack = [];
    public function startPush($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->extendPush($section, $content);
        }
    }
    public function stopPush()
    {
        if (empty($this->pushStack)) {
            throw new InvalidArgumentException('Cannot end a push stack without first starting one.');
        }
        return tap(array_pop($this->pushStack), function ($last) {
            $this->extendPush($last, ob_get_clean());
        });
    }
    protected function extendPush($section, $content)
    {
        if (! isset($this->pushes[$section])) {
            $this->pushes[$section] = [];
        }
        if (! isset($this->pushes[$section][$this->renderCount])) {
            $this->pushes[$section][$this->renderCount] = $content;
        } else {
            $this->pushes[$section][$this->renderCount] .= $content;
        }
    }
    public function startPrepend($section, $content = '')
    {
        if ($content === '') {
            if (ob_start()) {
                $this->pushStack[] = $section;
            }
        } else {
            $this->extendPrepend($section, $content);
        }
    }
    public function stopPrepend()
    {
        if (empty($this->pushStack)) {
            throw new InvalidArgumentException('Cannot end a prepend operation without first starting one.');
        }
        return tap(array_pop($this->pushStack), function ($last) {
            $this->extendPrepend($last, ob_get_clean());
        });
    }
    protected function extendPrepend($section, $content)
    {
        if (! isset($this->prepends[$section])) {
            $this->prepends[$section] = [];
        }
        if (! isset($this->prepends[$section][$this->renderCount])) {
            $this->prepends[$section][$this->renderCount] = $content;
        } else {
            $this->prepends[$section][$this->renderCount] = $content.$this->prepends[$section][$this->renderCount];
        }
    }
    public function yieldPushContent($section, $default = '')
    {
        if (! isset($this->pushes[$section]) && ! isset($this->prepends[$section])) {
            return $default;
        }
        $output = '';
        if (isset($this->prepends[$section])) {
            $output .= implode(array_reverse($this->prepends[$section]));
        }
        if (isset($this->pushes[$section])) {
            $output .= implode($this->pushes[$section]);
        }
        return $output;
    }
    public function flushStacks()
    {
        $this->pushes = [];
        $this->prepends = [];
        $this->pushStack = [];
    }
}
