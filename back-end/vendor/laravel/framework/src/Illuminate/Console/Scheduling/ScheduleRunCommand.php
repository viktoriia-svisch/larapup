<?php
namespace Illuminate\Console\Scheduling;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
class ScheduleRunCommand extends Command
{
    protected $name = 'schedule:run';
    protected $description = 'Run the scheduled commands';
    protected $schedule;
    protected $startedAt;
    protected $eventsRan = false;
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->startedAt = Carbon::now();
        parent::__construct();
    }
    public function handle()
    {
        foreach ($this->schedule->dueEvents($this->laravel) as $event) {
            if (! $event->filtersPass($this->laravel)) {
                continue;
            }
            if ($event->onOneServer) {
                $this->runSingleServerEvent($event);
            } else {
                $this->runEvent($event);
            }
            $this->eventsRan = true;
        }
        if (! $this->eventsRan) {
            $this->info('No scheduled commands are ready to run.');
        }
    }
    protected function runSingleServerEvent($event)
    {
        if ($this->schedule->serverShouldRun($event, $this->startedAt)) {
            $this->runEvent($event);
        } else {
            $this->line('<info>Skipping command (has already run on another server):</info> '.$event->getSummaryForDisplay());
        }
    }
    protected function runEvent($event)
    {
        $this->line('<info>Running scheduled command:</info> '.$event->getSummaryForDisplay());
        $event->run($this->laravel);
        $this->eventsRan = true;
    }
}
