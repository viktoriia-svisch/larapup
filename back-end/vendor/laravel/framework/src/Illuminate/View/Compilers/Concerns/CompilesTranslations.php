<?php
namespace Illuminate\View\Compilers\Concerns;
trait CompilesTranslations
{
    protected function compileLang($expression)
    {
        if (is_null($expression)) {
            return '<?php $__env->startTranslation(); ?>';
        } elseif ($expression[1] === '[') {
            return "<?php \$__env->startTranslation{$expression}; ?>";
        }
        return "<?php echo app('translator')->getFromJson{$expression}; ?>";
    }
    protected function compileEndlang()
    {
        return '<?php echo $__env->renderTranslation(); ?>';
    }
    protected function compileChoice($expression)
    {
        return "<?php echo app('translator')->choice{$expression}; ?>";
    }
}
