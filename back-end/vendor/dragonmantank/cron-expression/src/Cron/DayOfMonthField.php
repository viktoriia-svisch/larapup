<?php
namespace Cron;
use DateTime;
class DayOfMonthField extends AbstractField
{
    protected $rangeStart = 1;
    protected $rangeEnd = 31;
    private static function getNearestWeekday($currentYear, $currentMonth, $targetDay)
    {
        $tday = str_pad($targetDay, 2, '0', STR_PAD_LEFT);
        $target = DateTime::createFromFormat('Y-m-d', "$currentYear-$currentMonth-$tday");
        $currentWeekday = (int) $target->format('N');
        if ($currentWeekday < 6) {
            return $target;
        }
        $lastDayOfMonth = $target->format('t');
        foreach (array(-1, 1, -2, 2) as $i) {
            $adjusted = $targetDay + $i;
            if ($adjusted > 0 && $adjusted <= $lastDayOfMonth) {
                $target->setDate($currentYear, $currentMonth, $adjusted);
                if ($target->format('N') < 6 && $target->format('m') == $currentMonth) {
                    return $target;
                }
            }
        }
    }
    public function isSatisfiedBy(DateTime $date, $value)
    {
        if ($value == '?') {
            return true;
        }
        $fieldValue = $date->format('d');
        if ($value == 'L') {
            return $fieldValue == $date->format('t');
        }
        if (strpos($value, 'W')) {
            $targetDay = substr($value, 0, strpos($value, 'W'));
            return $date->format('j') == self::getNearestWeekday(
                $date->format('Y'),
                $date->format('m'),
                $targetDay
            )->format('j');
        }
        return $this->isSatisfied($date->format('d'), $value);
    }
    public function increment(DateTime $date, $invert = false)
    {
        if ($invert) {
            $date->modify('previous day');
            $date->setTime(23, 59);
        } else {
            $date->modify('next day');
            $date->setTime(0, 0);
        }
        return $this;
    }
    public function validate($value)
    {
        $basicChecks = parent::validate($value);
        if (strpos($value, ',') !== false && (strpos($value, 'W') !== false || strpos($value, 'L') !== false)) {
            return false;
        }
        if (!$basicChecks) {
            if ($value === 'L') {
                return true;
            }
            if (preg_match('/^(.*)W$/', $value, $matches)) {
                return $this->validate($matches[1]);
            }
            return false;
        }
        return $basicChecks;
    }
}
