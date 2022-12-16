<?php
namespace Illuminate\Console\Scheduling;
use Illuminate\Support\Carbon;
trait ManagesFrequencies
{
    public function cron($expression)
    {
        $this->expression = $expression;
        return $this;
    }
    public function between($startTime, $endTime)
    {
        return $this->when($this->inTimeInterval($startTime, $endTime));
    }
    public function unlessBetween($startTime, $endTime)
    {
        return $this->skip($this->inTimeInterval($startTime, $endTime));
    }
    private function inTimeInterval($startTime, $endTime)
    {
        return function () use ($startTime, $endTime) {
            return Carbon::now($this->timezone)->between(
                Carbon::parse($startTime, $this->timezone),
                Carbon::parse($endTime, $this->timezone),
                true
            );
        };
    }
    public function everyMinute()
    {
        return $this->spliceIntoPosition(1, '*');
    }
    public function everyFiveMinutes()
    {
        return $this->spliceIntoPosition(1, '*/5');
    }
    public function everyTenMinutes()
    {
        return $this->spliceIntoPosition(1, '*/10');
    }
    public function everyFifteenMinutes()
    {
        return $this->spliceIntoPosition(1, '*/15');
    }
    public function everyThirtyMinutes()
    {
        return $this->spliceIntoPosition(1, '0,30');
    }
    public function hourly()
    {
        return $this->spliceIntoPosition(1, 0);
    }
    public function hourlyAt($offset)
    {
        return $this->spliceIntoPosition(1, $offset);
    }
    public function daily()
    {
        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, 0);
    }
    public function at($time)
    {
        return $this->dailyAt($time);
    }
    public function dailyAt($time)
    {
        $segments = explode(':', $time);
        return $this->spliceIntoPosition(2, (int) $segments[0])
                    ->spliceIntoPosition(1, count($segments) === 2 ? (int) $segments[1] : '0');
    }
    public function twiceDaily($first = 1, $second = 13)
    {
        $hours = $first.','.$second;
        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, $hours);
    }
    public function weekdays()
    {
        return $this->spliceIntoPosition(5, '1-5');
    }
    public function weekends()
    {
        return $this->spliceIntoPosition(5, '0,6');
    }
    public function mondays()
    {
        return $this->days(1);
    }
    public function tuesdays()
    {
        return $this->days(2);
    }
    public function wednesdays()
    {
        return $this->days(3);
    }
    public function thursdays()
    {
        return $this->days(4);
    }
    public function fridays()
    {
        return $this->days(5);
    }
    public function saturdays()
    {
        return $this->days(6);
    }
    public function sundays()
    {
        return $this->days(0);
    }
    public function weekly()
    {
        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, 0)
                    ->spliceIntoPosition(5, 0);
    }
    public function weeklyOn($day, $time = '0:0')
    {
        $this->dailyAt($time);
        return $this->spliceIntoPosition(5, $day);
    }
    public function monthly()
    {
        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, 0)
                    ->spliceIntoPosition(3, 1);
    }
    public function monthlyOn($day = 1, $time = '0:0')
    {
        $this->dailyAt($time);
        return $this->spliceIntoPosition(3, $day);
    }
    public function twiceMonthly($first = 1, $second = 16)
    {
        $days = $first.','.$second;
        return $this->spliceIntoPosition(1, 0)
            ->spliceIntoPosition(2, 0)
            ->spliceIntoPosition(3, $days);
    }
    public function quarterly()
    {
        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, 0)
                    ->spliceIntoPosition(3, 1)
                    ->spliceIntoPosition(4, '1-12/3');
    }
    public function yearly()
    {
        return $this->spliceIntoPosition(1, 0)
                    ->spliceIntoPosition(2, 0)
                    ->spliceIntoPosition(3, 1)
                    ->spliceIntoPosition(4, 1);
    }
    public function days($days)
    {
        $days = is_array($days) ? $days : func_get_args();
        return $this->spliceIntoPosition(5, implode(',', $days));
    }
    public function timezone($timezone)
    {
        $this->timezone = $timezone;
        return $this;
    }
    protected function spliceIntoPosition($position, $value)
    {
        $segments = explode(' ', $this->expression);
        $segments[$position - 1] = $value;
        return $this->cron(implode(' ', $segments));
    }
}
