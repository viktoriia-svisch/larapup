<?php
namespace Illuminate\View\Compilers\Concerns;
trait CompilesRawPhp
{
    protected function compilePhp($expression)
    {
        if ($expression) {
            return "<?php {$expression}; ?>";
        }
        return '@php';
    }
    protected function compileUnset($expression)
    {
        return "<?php unset{$expression}; ?>";
    }
}
