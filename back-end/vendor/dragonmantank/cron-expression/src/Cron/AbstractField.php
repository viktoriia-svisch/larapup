<?php
namespace Cron;
abstract class AbstractField implements FieldInterface
{
    protected $fullRange = [];
    protected $literals = [];
    protected $rangeStart;
    protected $rangeEnd;
    public function __construct()
    {
        $this->fullRange = range($this->rangeStart, $this->rangeEnd);
    }
    public function isSatisfied($dateValue, $value)
    {
        if ($this->isIncrementsOfRanges($value)) {
            return $this->isInIncrementsOfRanges($dateValue, $value);
        } elseif ($this->isRange($value)) {
            return $this->isInRange($dateValue, $value);
        }
        return $value == '*' || $dateValue == $value;
    }
    public function isRange($value)
    {
        return strpos($value, '-') !== false;
    }
    public function isIncrementsOfRanges($value)
    {
        return strpos($value, '/') !== false;
    }
    public function isInRange($dateValue, $value)
    {
        $parts = array_map(function($value) {
                $value = trim($value);
                $value = $this->convertLiterals($value);
                return $value;
            },
            explode('-', $value, 2)
        );
        return $dateValue >= $parts[0] && $dateValue <= $parts[1];
    }
    public function isInIncrementsOfRanges($dateValue, $value)
    {
        $chunks = array_map('trim', explode('/', $value, 2));
        $range = $chunks[0];
        $step = isset($chunks[1]) ? $chunks[1] : 0;
        if (is_null($step) || '0' === $step || 0 === $step) {
            return false;
        }
        if ('*' == $range) {
            $range = $this->rangeStart . '-' . $this->rangeEnd;
        }
        $rangeChunks = explode('-', $range, 2);
        $rangeStart = $rangeChunks[0];
        $rangeEnd = isset($rangeChunks[1]) ? $rangeChunks[1] : $rangeStart;
        if ($rangeStart < $this->rangeStart || $rangeStart > $this->rangeEnd || $rangeStart > $rangeEnd) {
            throw new \OutOfRangeException('Invalid range start requested');
        }
        if ($rangeEnd < $this->rangeStart || $rangeEnd > $this->rangeEnd || $rangeEnd < $rangeStart) {
            throw new \OutOfRangeException('Invalid range end requested');
        }
        if ($step >= $this->rangeEnd) {
            $thisRange = [$this->fullRange[$step % count($this->fullRange)]];
        } else {
            $thisRange = range($rangeStart, $rangeEnd, $step);
        }
        return in_array($dateValue, $thisRange);
    }
    public function getRangeForExpression($expression, $max)
    {
        $values = array();
        $expression = $this->convertLiterals($expression);
        if (strpos($expression, ',') !== false) {
            $ranges = explode(',', $expression);
            $values = [];
            foreach ($ranges as $range) {
                $expanded = $this->getRangeForExpression($range, $this->rangeEnd);
                $values = array_merge($values, $expanded);
            }
            return $values;
        }
        if ($this->isRange($expression) || $this->isIncrementsOfRanges($expression)) {
            if (!$this->isIncrementsOfRanges($expression)) {
                list ($offset, $to) = explode('-', $expression);
                $offset = $this->convertLiterals($offset);
                $to = $this->convertLiterals($to);
                $stepSize = 1;
            }
            else {
                $range = array_map('trim', explode('/', $expression, 2));
                $stepSize = isset($range[1]) ? $range[1] : 0;
                $range = $range[0];
                $range = explode('-', $range, 2);
                $offset = $range[0];
                $to = isset($range[1]) ? $range[1] : $max;
            }
            $offset = $offset == '*' ? $this->rangeStart : $offset;
            if ($stepSize >= $this->rangeEnd) {
                $values = [$this->fullRange[$stepSize % count($this->fullRange)]];
            } else {
                for ($i = $offset; $i <= $to; $i += $stepSize) {
                    $values[] = (int)$i;
                }
            }
            sort($values);
        }
        else {
            $values = array($expression);
        }
        return $values;
    }
    protected function convertLiterals($value)
    {
        if (count($this->literals)) {
            $key = array_search($value, $this->literals);
            if ($key !== false) {
                return $key;
            }
        }
        return $value;
    }
    public function validate($value)
    {
        $value = $this->convertLiterals($value);
        if ('*' === $value) {
            return true;
        }
        if (strpos($value, '/') !== false) {
            list($range, $step) = explode('/', $value);
            return $this->validate($range) && filter_var($step, FILTER_VALIDATE_INT);
        }
        if (strpos($value, ',') !== false) {
            foreach (explode(',', $value) as $listItem) {
                if (!$this->validate($listItem)) {
                    return false;
                }
            }
            return true;
        }
        if (strpos($value, '-') !== false) {
            if (substr_count($value, '-') > 1) {
                return false;
            }
            $chunks = explode('-', $value);
            $chunks[0] = $this->convertLiterals($chunks[0]);
            $chunks[1] = $this->convertLiterals($chunks[1]);
            if ('*' == $chunks[0] || '*' == $chunks[1]) {
                return false;
            }
            return $this->validate($chunks[0]) && $this->validate($chunks[1]);
        }
        if (!is_numeric($value)) {
            return false;
        }
        if (is_float($value) || strpos($value, '.') !== false) {
            return false;
        }
        $value = (int) $value;
        return in_array($value, $this->fullRange, true);
    }
}
