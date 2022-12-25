<?php
namespace Illuminate\Console\Scheduling;
use Illuminate\Console\Application;
use Illuminate\Support\ProcessUtils;
class CommandBuilder
{
    public function buildCommand(Event $event)
    {
        if ($event->runInBackground) {
            return $this->buildBackgroundCommand($event);
        }
        return $this->buildForegroundCommand($event);
    }
    protected function buildForegroundCommand(Event $event)
    {
        $output = ProcessUtils::escapeArgument($event->output);
        return $this->ensureCorrectUser(
            $event, $event->command.($event->shouldAppendOutput ? ' >> ' : ' > ').$output.' 2>&1'
        );
    }
    protected function buildBackgroundCommand(Event $event)
    {
        $output = ProcessUtils::escapeArgument($event->output);
        $redirect = $event->shouldAppendOutput ? ' >> ' : ' > ';
        $finished = Application::formatCommandString('schedule:finish').' "'.$event->mutexName().'"';
        return $this->ensureCorrectUser($event,
            '('.$event->command.$redirect.$output.' 2>&1 '.(windows_os() ? '&' : ';').' '.$finished.') > '
            .ProcessUtils::escapeArgument($event->getDefaultOutput()).' 2>&1 &'
        );
    }
    protected function ensureCorrectUser(Event $event, $command)
    {
        return $event->user && ! windows_os() ? 'sudo -u '.$event->user.' -- sh -c \''.$command.'\'' : $command;
    }
}
