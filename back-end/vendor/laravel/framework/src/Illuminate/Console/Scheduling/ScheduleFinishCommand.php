<?php
namespace Illuminate\Console\Scheduling;
use Illuminate\Console\Command;
class ScheduleFinishCommand extends Command
{
    protected $signature = 'schedule:finish {id}';
    protected $description = 'Handle the completion of a scheduled command';
    protected $hidden = true;
    protected $schedule;
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        parent::__construct();
    }
    public function handle()
    {
        collect($this->schedule->events())->filter(function ($value) {
            return $value->mutexName() == $this->argument('id');
        })->each->callAfterCallbacks($this->laravel);
    }
}
