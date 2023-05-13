<?php
namespace Illuminate\Console;
use Closure;
trait ConfirmableTrait
{
    public function confirmToProceed($warning = 'Application In Production!', $callback = null)
    {
        $callback = is_null($callback) ? $this->getDefaultConfirmCallback() : $callback;
        $shouldConfirm = $callback instanceof Closure ? call_user_func($callback) : $callback;
        if ($shouldConfirm) {
            if ($this->option('force')) {
                return true;
            }
            $this->alert($warning);
            $confirmed = $this->confirm('Do you really wish to run this command?');
            if (! $confirmed) {
                $this->comment('Command Cancelled!');
                return false;
            }
        }
        return true;
    }
    protected function getDefaultConfirmCallback()
    {
        return function () {
            return $this->getLaravel()->environment() === 'production';
        };
    }
}
