<?php
namespace Psy\ExecutionLoop;
use Psy\Shell;
interface Listener
{
    public static function isSupported();
    public function beforeRun(Shell $shell);
    public function beforeLoop(Shell $shell);
    public function onInput(Shell $shell, $input);
    public function onExecute(Shell $shell, $code);
    public function afterLoop(Shell $shell);
    public function afterRun(Shell $shell);
}
