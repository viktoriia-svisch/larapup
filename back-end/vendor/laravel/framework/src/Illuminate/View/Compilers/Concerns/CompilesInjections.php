<?php
namespace Illuminate\View\Compilers\Concerns;
trait CompilesInjections
{
    protected function compileInject($expression)
    {
        $segments = explode(',', preg_replace("/[\(\)\\\"\']/", '', $expression));
        $variable = trim($segments[0]);
        $service = trim($segments[1]);
        return "<?php \${$variable} = app('{$service}'); ?>";
    }
}
