<?php
namespace App\Helpers;
use DateTime;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
class DateTimeHelper
{
    public static function getDateOfNextMonth($date, $add = 1)
    {
        $date = strtotime($date);
        $day = date('d', $date);
        $month = strtotime('first day of this month', $date);
        $month = strtotime('+' . $add . ' month', $month);
        if ($day == 31 || ($day >= 28 && date('m', $date) == 2)) {
            $day = date('d', strtotime('last day of this month', $month));
        } else if ($day >= 29 && date('m', $month) == 2) {
            $day = (date('Y', $month) % 4 == 0) ? 29 : 28;
        }
        return date('Y-m-', $month) . $day;
    }
    public static function getLastDateOnNextMonth()
    {
        return date('Y-m-t', strtotime('next month'));
    }
    public function checkMonthDayWithoutZero($month, $day)
    {
        $format = 'Y-n-j';
        $dateStr = date("Y") . '-' . $month . '-' . $day;
        $d = DateTime::createFromFormat($format, $dateStr);
        if (!$d || $d->format($format) != $dateStr) {
            return false;    
        }
        return true;
    }
    public function validateMonthDayWithoutZero($month, $day, $message = '')
    {
        $valid = $this->checkMonthDayWithoutZero($month, $day);
        if (!$valid) {
            $message = empty($message) ? 'Day is invalid' : $message;
            $v = \Validator::make(
                ['day' => -1],
                ['day' => 'in:1,31'],
                ['day.in' => $message]
            );
            throw new ValidationException($v);
        }
        return true;
    }
    public static function formatDate($value)
    {
        if (empty($value)) {
            return '';
        }
        $date = strtotime($value);
        return date('d/m/Y', $date);
    }
	public static function formatDateTime($value)
	{
		if (empty($value)) {
			return '';
		}
		$date = strtotime($value);
		return date('d/m/Y (H:i:s)', $date);
	}
	public static function isNowPassedDate($date){
		$dateDiffSecond = Carbon::now()->getTimestamp() -  strtotime($date);
		return $dateDiffSecond > 0;
	}
}
