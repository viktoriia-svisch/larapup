<?php
namespace Cron;
use DateTime;
use InvalidArgumentException;
class DayOfWeekField extends AbstractField
{
    protected $rangeStart = 0;
    protected $rangeEnd = 7;
    protected $nthRange;
    protected $literals = [1 => 'MON', 2 => 'TUE', 3 => 'WED', 4 => 'THU', 5 => 'FRI', 6 => 'SAT', 7 => 'SUN'];
    public function __construct()
    {
        $this->nthRange = range(1, 5);
        parent::__construct();
    }
    public function isSatisfiedBy(DateTime $date, $value)
    {
        if ($value == '?') {
            return true;
        }
        $value = $this->convertLiterals($value);
        $currentYear = $date->format('Y');
        $currentMonth = $date->format('m');
        $lastDayOfMonth = $date->format('t');
        if (strpos($value, 'L')) {
            $weekday = str_replace('7', '0', substr($value, 0, strpos($value, 'L')));
            $tdate = clone $date;
            $tdate->setDate($currentYear, $currentMonth, $lastDayOfMonth);
            while ($tdate->format('w') != $weekday) {
                $tdateClone = new DateTime();
                $tdate = $tdateClone
                    ->setTimezone($tdate->getTimezone())
                    ->setDate($currentYear, $currentMonth, --$lastDayOfMonth);
            }
            return $date->format('j') == $lastDayOfMonth;
        }
        if (strpos($value, '#')) {
            list($weekday, $nth) = explode('#', $value);
            if (!is_numeric($nth)) {
                throw new InvalidArgumentException("Hashed weekdays must be numeric, {$nth} given");
            } else {
                $nth = (int) $nth;
            }
            if ($weekday === '0') {
                $weekday = 7;
            }
            $weekday = $this->convertLiterals($weekday);
            if ($weekday < 0 || $weekday > 7) {
                throw new InvalidArgumentException("Weekday must be a value between 0 and 7. {$weekday} given");
            }
            if (!in_array($nth, $this->nthRange)) {
                throw new InvalidArgumentException("There are never more than 5 or less than 1 of a given weekday in a month, {$nth} given");
            }
            if ($date->format('N') != $weekday) {
                return false;
            }
            $tdate = clone $date;
            $tdate->setDate($currentYear, $currentMonth, 1);
            $dayCount = 0;
            $currentDay = 1;
            while ($currentDay < $lastDayOfMonth + 1) {
                if ($tdate->format('N') == $weekday) {
                    if (++$dayCount >= $nth) {
                        break;
                    }
                }
                $tdate->setDate($currentYear, $currentMonth, ++$currentDay);
            }
            return $date->format('j') == $currentDay;
        }
        if (strpos($value, '-')) {
            $parts = explode('-', $value);
            if ($parts[0] == '7') {
                $parts[0] = '0';
            } elseif ($parts[1] == '0') {
                $parts[1] = '7';
            }
            $value = implode('-', $parts);
        }
        $format = in_array(7, str_split($value)) ? 'N' : 'w';
        $fieldValue = $date->format($format);
        return $this->isSatisfied($fieldValue, $value);
    }
    public function increment(DateTime $date, $invert = false)
    {
        if ($invert) {
            $date->modify('-1 day');
            $date->setTime(23, 59, 0);
        } else {
            $date->modify('+1 day');
            $date->setTime(0, 0, 0);
        }
        return $this;
    }
    public function validate($value)
    {
        $basicChecks = parent::validate($value);
        if (!$basicChecks) {
            if (strpos($value, '#') !== false) {
                $chunks = explode('#', $value);
                $chunks[0] = $this->convertLiterals($chunks[0]);
                if (parent::validate($chunks[0]) && is_numeric($chunks[1]) && in_array($chunks[1], $this->nthRange)) {
                    return true;
                }
            }
            if (preg_match('/^(.*)L$/', $value, $matches)) {
                return $this->validate($matches[1]);
            }
            return false;
        }
        return $basicChecks;
    }
}
