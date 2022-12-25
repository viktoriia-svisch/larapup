<?php
namespace Psy;
class ExecutionClosure
{
    const NOOP_INPUT = 'return null;';
    private $closure;
    public function __construct(Shell $__psysh__)
    {
        $this->setClosure($__psysh__, function () use ($__psysh__) {
            try {
                \extract($__psysh__->getScopeVariables(false));
                \ob_start([$__psysh__, 'writeStdout'], 1);
                \set_error_handler([$__psysh__, 'handleError']);
                $_ = eval($__psysh__->onExecute($__psysh__->flushCode() ?: ExecutionClosure::NOOP_INPUT));
            } catch (\Throwable $_e) {
                \restore_error_handler();
                if (\ob_get_level() > 0) {
                    \ob_end_clean();
                }
                throw $_e;
            } catch (\Exception $_e) {
                \restore_error_handler();
                if (\ob_get_level() > 0) {
                    \ob_end_clean();
                }
                throw $_e;
            }
            \restore_error_handler();
            \ob_end_flush();
            $__psysh__->setScopeVariables(\get_defined_vars());
            return $_;
        });
    }
    protected function setClosure(Shell $shell, \Closure $closure)
    {
        if (self::shouldBindClosure()) {
            $that = $shell->getBoundObject();
            if (\is_object($that)) {
                $closure = $closure->bindTo($that, \get_class($that));
            } else {
                $closure = $closure->bindTo(null, $shell->getBoundClass());
            }
        }
        $this->closure = $closure;
    }
    public function execute()
    {
        $closure = $this->closure;
        return $closure();
    }
    protected static function shouldBindClosure()
    {
        if (\defined('HHVM_VERSION')) {
            return \version_compare(HHVM_VERSION, '3.5.0', '>=');
        }
        return true;
    }
}
