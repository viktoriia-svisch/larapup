<?php
namespace Illuminate\View\Compilers\Concerns;
trait CompilesConditionals
{
    protected $firstCaseInSwitch = true;
    protected function compileAuth($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;
        return "<?php if(auth()->guard{$guard}->check()): ?>";
    }
    protected function compileElseAuth($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;
        return "<?php elseif(auth()->guard{$guard}->check()): ?>";
    }
    protected function compileEndAuth()
    {
        return '<?php endif; ?>';
    }
    protected function compileGuest($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;
        return "<?php if(auth()->guard{$guard}->guest()): ?>";
    }
    protected function compileElseGuest($guard = null)
    {
        $guard = is_null($guard) ? '()' : $guard;
        return "<?php elseif(auth()->guard{$guard}->guest()): ?>";
    }
    protected function compileEndGuest()
    {
        return '<?php endif; ?>';
    }
    protected function compileHasSection($expression)
    {
        return "<?php if (! empty(trim(\$__env->yieldContent{$expression}))): ?>";
    }
    protected function compileIf($expression)
    {
        return "<?php if{$expression}: ?>";
    }
    protected function compileUnless($expression)
    {
        return "<?php if (! {$expression}): ?>";
    }
    protected function compileElseif($expression)
    {
        return "<?php elseif{$expression}: ?>";
    }
    protected function compileElse()
    {
        return '<?php else: ?>';
    }
    protected function compileEndif()
    {
        return '<?php endif; ?>';
    }
    protected function compileEndunless()
    {
        return '<?php endif; ?>';
    }
    protected function compileIsset($expression)
    {
        return "<?php if(isset{$expression}): ?>";
    }
    protected function compileEndIsset()
    {
        return '<?php endif; ?>';
    }
    protected function compileSwitch($expression)
    {
        $this->firstCaseInSwitch = true;
        return "<?php switch{$expression}:";
    }
    protected function compileCase($expression)
    {
        if ($this->firstCaseInSwitch) {
            $this->firstCaseInSwitch = false;
            return "case {$expression}: ?>";
        }
        return "<?php case {$expression}: ?>";
    }
    protected function compileDefault()
    {
        return '<?php default: ?>';
    }
    protected function compileEndSwitch()
    {
        return '<?php endswitch; ?>';
    }
}
