<?php
namespace Psy;
use Psy\Exception\BreakException;
use Psy\Exception\ErrorException;
use Psy\Exception\ThrowUpException;
use Psy\Exception\TypeErrorException;
class ExecutionLoopClosure extends ExecutionClosure
{
    public function __construct(Shell $__psysh__)
    {
        $this->setClosure($__psysh__, function () use ($__psysh__) {
            \extract($__psysh__->getScopeVariables(false));
            do {
                $__psysh__->beforeLoop();
                try {
                    $__psysh__->getInput();
                    try {
                        if ($__psysh__->getLastExecSuccess()) {
                            \extract($__psysh__->getScopeVariablesDiff(\get_defined_vars()));
                        }
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
                    $__psysh__->writeReturnValue($_);
                } catch (BreakException $_e) {
                    $__psysh__->writeException($_e);
                    return;
                } catch (ThrowUpException $_e) {
                    $__psysh__->writeException($_e);
                    throw $_e;
                } catch (\TypeError $_e) {
                    $__psysh__->writeException(TypeErrorException::fromTypeError($_e));
                } catch (\Error $_e) {
                    $__psysh__->writeException(ErrorException::fromError($_e));
                } catch (\Exception $_e) {
                    $__psysh__->writeException($_e);
                }
                $__psysh__->afterLoop();
            } while (true);
        });
    }
}
