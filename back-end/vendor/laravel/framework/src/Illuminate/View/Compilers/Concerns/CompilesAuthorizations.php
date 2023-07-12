<?php
namespace Illuminate\View\Compilers\Concerns;
trait CompilesAuthorizations
{
    protected function compileCan($expression)
    {
        return "<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check{$expression}): ?>";
    }
    protected function compileCannot($expression)
    {
        return "<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->denies{$expression}): ?>";
    }
    protected function compileCanany($expression)
    {
        return "<?php if (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->any{$expression}): ?>";
    }
    protected function compileElsecan($expression)
    {
        return "<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->check{$expression}): ?>";
    }
    protected function compileElsecannot($expression)
    {
        return "<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->denies{$expression}): ?>";
    }
    protected function compileElsecanany($expression)
    {
        return "<?php elseif (app(\Illuminate\\Contracts\\Auth\\Access\\Gate::class)->any{$expression}): ?>";
    }
    protected function compileEndcan()
    {
        return '<?php endif; ?>';
    }
    protected function compileEndcannot()
    {
        return '<?php endif; ?>';
    }
    protected function compileEndcanany()
    {
        return '<?php endif; ?>';
    }
}
