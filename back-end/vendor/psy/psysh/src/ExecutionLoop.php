<?php
namespace Psy;
use Psy\Exception\ErrorException;
class ExecutionLoop
{
    public function run(Shell $shell)
    {
        $this->loadIncludes($shell);
        $closure = new ExecutionLoopClosure($shell);
        $closure->execute();
    }
    protected function loadIncludes(Shell $shell)
    {
        $load = function (Shell $__psysh__) {
            \set_error_handler([$__psysh__, 'handleError']);
            foreach ($__psysh__->getIncludes() as $__psysh_include__) {
                try {
                    include $__psysh_include__;
                } catch (\Error $_e) {
                    $__psysh__->writeException(ErrorException::fromError($_e));
                } catch (\Exception $_e) {
                    $__psysh__->writeException($_e);
                }
            }
            \restore_error_handler();
            unset($__psysh_include__);
            \extract($__psysh__->getScopeVariables(false));
            $__psysh__->setScopeVariables(\get_defined_vars());
        };
        $load($shell);
    }
}
