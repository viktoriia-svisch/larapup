<?php
namespace Cron;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;
use InvalidArgumentException;
use RuntimeException;
class CronExpression
{
    const MINUTE = 0;
    const HOUR = 1;
    const DAY = 2;
    const MONTH = 3;
    const WEEKDAY = 4;
    const YEAR = 5;
    private $cronParts;
    private $fieldFactory;
    private $maxIterationCount = 1000;
    private static $order = array(self::YEAR, self::MONTH, self::DAY, self::WEEKDAY, self::HOUR, self::MINUTE);
    public static function factory($expression, FieldFactory $fieldFactory = null)
    {
        $mappings = array(
            '@yearly' => '0 0 1 1 *',
            '@annually' => '0 0 1 1 *',
            '@monthly' => '0 0 1 * *',
            '@weekly' => '0 0 * * 0',
            '@daily' => '0 0 * * *',
            '@hourly' => '0 * * * *'
        );
        if (isset($mappings[$expression])) {
            $expression = $mappings[$expression];
        }
        return new static($expression, $fieldFactory ?: new FieldFactory());
    }
    public static function isValidExpression($expression)
    {
        try {
            self::factory($expression);
        } catch (InvalidArgumentException $e) {
            return false;
        }
        return true;
    }
    public function __construct($expression, FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;
        $this->setExpression($expression);
    }
    public function setExpression($value)
    {
        $this->cronParts = preg_split('/\s/', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (count($this->cronParts) < 5) {
            throw new InvalidArgumentException(
                $value . ' is not a valid CRON expression'
            );
        }
        foreach ($this->cronParts as $position => $part) {
            $this->setPart($position, $part);
        }
        return $this;
    }
    public function setPart($position, $value)
    {
        if (!$this->fieldFactory->getField($position)->validate($value)) {
            throw new InvalidArgumentException(
                'Invalid CRON field value ' . $value . ' at position ' . $position
            );
        }
        $this->cronParts[$position] = $value;
        return $this;
    }
    public function setMaxIterationCount($maxIterationCount)
    {
        $this->maxIterationCount = $maxIterationCount;
        return $this;
    }
    public function getNextRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false, $timeZone = null)
    {
        return $this->getRunDate($currentTime, $nth, false, $allowCurrentDate, $timeZone);
    }
    public function getPreviousRunDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false, $timeZone = null)
    {
        return $this->getRunDate($currentTime, $nth, true, $allowCurrentDate, $timeZone);
    }
    public function getMultipleRunDates($total, $currentTime = 'now', $invert = false, $allowCurrentDate = false, $timeZone = null)
    {
        $matches = array();
        for ($i = 0; $i < max(0, $total); $i++) {
            try {
                $matches[] = $this->getRunDate($currentTime, $i, $invert, $allowCurrentDate, $timeZone);
            } catch (RuntimeException $e) {
                break;
            }
        }
        return $matches;
    }
    public function getExpression($part = null)
    {
        if (null === $part) {
            return implode(' ', $this->cronParts);
        } elseif (array_key_exists($part, $this->cronParts)) {
            return $this->cronParts[$part];
        }
        return null;
    }
    public function __toString()
    {
        return $this->getExpression();
    }
    public function isDue($currentTime = 'now', $timeZone = null)
    {
        $timeZone = $this->determineTimeZone($currentTime, $timeZone);
        if ('now' === $currentTime) {
            $currentTime = new DateTime();
        } elseif ($currentTime instanceof DateTime) {
        } elseif ($currentTime instanceof DateTimeImmutable) {
            $currentTime = DateTime::createFromFormat('U', $currentTime->format('U'));
        } else {
            $currentTime = new DateTime($currentTime);
        }
        $currentTime->setTimeZone(new DateTimeZone($timeZone));
        $currentTime = DateTime::createFromFormat('Y-m-d H:i', $currentTime->format('Y-m-d H:i'));
        try {
            return $this->getNextRunDate($currentTime, 0, true)->getTimestamp() === $currentTime->getTimestamp();
        } catch (Exception $e) {
            return false;
        }
    }
    protected function getRunDate($currentTime = null, $nth = 0, $invert = false, $allowCurrentDate = false, $timeZone = null)
    {
        $timeZone = $this->determineTimeZone($currentTime, $timeZone);
        if ($currentTime instanceof DateTime) {
            $currentDate = clone $currentTime;
        } elseif ($currentTime instanceof DateTimeImmutable) {
            $currentDate = DateTime::createFromFormat('U', $currentTime->format('U'));
        } else {
            $currentDate = new DateTime($currentTime ?: 'now');
        }
        $currentDate->setTimeZone(new DateTimeZone($timeZone));
        $currentDate->setTime($currentDate->format('H'), $currentDate->format('i'), 0);
        $nextRun = clone $currentDate;
        $nth = (int) $nth;
        $parts = array();
        $fields = array();
        foreach (self::$order as $position) {
            $part = $this->getExpression($position);
            if (null === $part || '*' === $part) {
                continue;
            }
            $parts[$position] = $part;
            $fields[$position] = $this->fieldFactory->getField($position);
        }
        for ($i = 0; $i < $this->maxIterationCount; $i++) {
            foreach ($parts as $position => $part) {
                $satisfied = false;
                $field = $fields[$position];
                if (strpos($part, ',') === false) {
                    $satisfied = $field->isSatisfiedBy($nextRun, $part);
                } else {
                    foreach (array_map('trim', explode(',', $part)) as $listPart) {
                        if ($field->isSatisfiedBy($nextRun, $listPart)) {
                            $satisfied = true;
                            break;
                        }
                    }
                }
                if (!$satisfied) {
                    $field->increment($nextRun, $invert, $part);
                    continue 2;
                }
            }
            if ((!$allowCurrentDate && $nextRun == $currentDate) || --$nth > -1) {
                $this->fieldFactory->getField(0)->increment($nextRun, $invert, isset($parts[0]) ? $parts[0] : null);
                continue;
            }
            return $nextRun;
        }
        throw new RuntimeException('Impossible CRON expression');
    }
    protected function determineTimeZone($currentTime, $timeZone)
    {
        if (! is_null($timeZone)) {
            return $timeZone;
        }
        if ($currentTime instanceOf Datetime) {
            return $currentTime->getTimeZone()->getName();
        }
        return date_default_timezone_get();
    }
}
