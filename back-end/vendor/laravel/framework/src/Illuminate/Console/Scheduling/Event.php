<?php
namespace Illuminate\Console\Scheduling;
use Closure;
use Cron\CronExpression;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use GuzzleHttp\Client as HttpClient;
use Illuminate\Contracts\Mail\Mailer;
use Symfony\Component\Process\Process;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Container\Container;
class Event
{
    use Macroable, ManagesFrequencies;
    public $command;
    public $expression = '* * * * *';
    public $timezone;
    public $user;
    public $environments = [];
    public $evenInMaintenanceMode = false;
    public $withoutOverlapping = false;
    public $onOneServer = false;
    public $expiresAt = 1440;
    public $runInBackground = false;
    protected $filters = [];
    protected $rejects = [];
    public $output = '/dev/null';
    public $shouldAppendOutput = false;
    protected $beforeCallbacks = [];
    protected $afterCallbacks = [];
    public $description;
    public $mutex;
    public function __construct(EventMutex $mutex, $command)
    {
        $this->mutex = $mutex;
        $this->command = $command;
        $this->output = $this->getDefaultOutput();
    }
    public function getDefaultOutput()
    {
        return (DIRECTORY_SEPARATOR === '\\') ? 'NUL' : '/dev/null';
    }
    public function run(Container $container)
    {
        if ($this->withoutOverlapping &&
            ! $this->mutex->create($this)) {
            return;
        }
        $this->runInBackground
                    ? $this->runCommandInBackground($container)
                    : $this->runCommandInForeground($container);
    }
    public function mutexName()
    {
        return 'framework'.DIRECTORY_SEPARATOR.'schedule-'.sha1($this->expression.$this->command);
    }
    protected function runCommandInForeground(Container $container)
    {
        $this->callBeforeCallbacks($container);
        (new Process(
            $this->buildCommand(), base_path(), null, null, null
        ))->run();
        $this->callAfterCallbacks($container);
    }
    protected function runCommandInBackground(Container $container)
    {
        $this->callBeforeCallbacks($container);
        (new Process(
            $this->buildCommand(), base_path(), null, null, null
        ))->run();
    }
    public function callBeforeCallbacks(Container $container)
    {
        foreach ($this->beforeCallbacks as $callback) {
            $container->call($callback);
        }
    }
    public function callAfterCallbacks(Container $container)
    {
        foreach ($this->afterCallbacks as $callback) {
            $container->call($callback);
        }
    }
    public function buildCommand()
    {
        return (new CommandBuilder)->buildCommand($this);
    }
    public function isDue($app)
    {
        if (! $this->runsInMaintenanceMode() && $app->isDownForMaintenance()) {
            return false;
        }
        return $this->expressionPasses() &&
               $this->runsInEnvironment($app->environment());
    }
    public function runsInMaintenanceMode()
    {
        return $this->evenInMaintenanceMode;
    }
    protected function expressionPasses()
    {
        $date = Carbon::now();
        if ($this->timezone) {
            $date->setTimezone($this->timezone);
        }
        return CronExpression::factory($this->expression)->isDue($date->toDateTimeString());
    }
    public function runsInEnvironment($environment)
    {
        return empty($this->environments) || in_array($environment, $this->environments);
    }
    public function filtersPass($app)
    {
        foreach ($this->filters as $callback) {
            if (! $app->call($callback)) {
                return false;
            }
        }
        foreach ($this->rejects as $callback) {
            if ($app->call($callback)) {
                return false;
            }
        }
        return true;
    }
    public function storeOutput()
    {
        $this->ensureOutputIsBeingCaptured();
        return $this;
    }
    public function sendOutputTo($location, $append = false)
    {
        $this->output = $location;
        $this->shouldAppendOutput = $append;
        return $this;
    }
    public function appendOutputTo($location)
    {
        return $this->sendOutputTo($location, true);
    }
    public function emailOutputTo($addresses, $onlyIfOutputExists = false)
    {
        $this->ensureOutputIsBeingCapturedForEmail();
        $addresses = Arr::wrap($addresses);
        return $this->then(function (Mailer $mailer) use ($addresses, $onlyIfOutputExists) {
            $this->emailOutput($mailer, $addresses, $onlyIfOutputExists);
        });
    }
    public function emailWrittenOutputTo($addresses)
    {
        return $this->emailOutputTo($addresses, true);
    }
    protected function ensureOutputIsBeingCapturedForEmail()
    {
        $this->ensureOutputIsBeingCaptured();
    }
    protected function ensureOutputIsBeingCaptured()
    {
        if (is_null($this->output) || $this->output == $this->getDefaultOutput()) {
            $this->sendOutputTo(storage_path('logs/schedule-'.sha1($this->mutexName()).'.log'));
        }
    }
    protected function emailOutput(Mailer $mailer, $addresses, $onlyIfOutputExists = false)
    {
        $text = file_exists($this->output) ? file_get_contents($this->output) : '';
        if ($onlyIfOutputExists && empty($text)) {
            return;
        }
        $mailer->raw($text, function ($m) use ($addresses) {
            $m->to($addresses)->subject($this->getEmailSubject());
        });
    }
    protected function getEmailSubject()
    {
        if ($this->description) {
            return $this->description;
        }
        return "Scheduled Job Output For [{$this->command}]";
    }
    public function pingBefore($url)
    {
        return $this->before(function () use ($url) {
            (new HttpClient)->get($url);
        });
    }
    public function pingBeforeIf($value, $url)
    {
        return $value ? $this->pingBefore($url) : $this;
    }
    public function thenPing($url)
    {
        return $this->then(function () use ($url) {
            (new HttpClient)->get($url);
        });
    }
    public function thenPingIf($value, $url)
    {
        return $value ? $this->thenPing($url) : $this;
    }
    public function runInBackground()
    {
        $this->runInBackground = true;
        return $this;
    }
    public function user($user)
    {
        $this->user = $user;
        return $this;
    }
    public function environments($environments)
    {
        $this->environments = is_array($environments) ? $environments : func_get_args();
        return $this;
    }
    public function evenInMaintenanceMode()
    {
        $this->evenInMaintenanceMode = true;
        return $this;
    }
    public function withoutOverlapping($expiresAt = 1440)
    {
        $this->withoutOverlapping = true;
        $this->expiresAt = $expiresAt;
        return $this->then(function () {
            $this->mutex->forget($this);
        })->skip(function () {
            return $this->mutex->exists($this);
        });
    }
    public function onOneServer()
    {
        $this->onOneServer = true;
        return $this;
    }
    public function when($callback)
    {
        $this->filters[] = is_callable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };
        return $this;
    }
    public function skip($callback)
    {
        $this->rejects[] = is_callable($callback) ? $callback : function () use ($callback) {
            return $callback;
        };
        return $this;
    }
    public function before(Closure $callback)
    {
        $this->beforeCallbacks[] = $callback;
        return $this;
    }
    public function after(Closure $callback)
    {
        return $this->then($callback);
    }
    public function then(Closure $callback)
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }
    public function name($description)
    {
        return $this->description($description);
    }
    public function description($description)
    {
        $this->description = $description;
        return $this;
    }
    public function getSummaryForDisplay()
    {
        if (is_string($this->description)) {
            return $this->description;
        }
        return $this->buildCommand();
    }
    public function nextRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false)
    {
        return Carbon::instance(CronExpression::factory(
            $this->getExpression()
        )->getNextRunDate($currentTime, $nth, $allowCurrentDate, $this->timezone));
    }
    public function getExpression()
    {
        return $this->expression;
    }
    public function preventOverlapsUsing(EventMutex $mutex)
    {
        $this->mutex = $mutex;
        return $this;
    }
}
