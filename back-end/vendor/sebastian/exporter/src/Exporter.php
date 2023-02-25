<?php
namespace SebastianBergmann\Exporter;
use SebastianBergmann\RecursionContext\Context;
class Exporter
{
    public function export($value, $indentation = 0)
    {
        return $this->recursiveExport($value, $indentation);
    }
    public function shortenedRecursiveExport(&$data, Context $context = null)
    {
        $result   = [];
        $exporter = new self();
        if (!$context) {
            $context = new Context;
        }
        $array = $data;
        $context->add($data);
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if ($context->contains($data[$key]) !== false) {
                    $result[] = '*RECURSION*';
                } else {
                    $result[] = sprintf(
                        'array(%s)',
                        $this->shortenedRecursiveExport($data[$key], $context)
                    );
                }
            } else {
                $result[] = $exporter->shortenedExport($value);
            }
        }
        return implode(', ', $result);
    }
    public function shortenedExport($value)
    {
        if (is_string($value)) {
            $string = str_replace("\n", '', $this->export($value));
            if (function_exists('mb_strlen')) {
                if (mb_strlen($string) > 40) {
                    $string = mb_substr($string, 0, 30) . '...' . mb_substr($string, -7);
                }
            } else {
                if (strlen($string) > 40) {
                    $string = substr($string, 0, 30) . '...' . substr($string, -7);
                }
            }
            return $string;
        }
        if (is_object($value)) {
            return sprintf(
                '%s Object (%s)',
                get_class($value),
                count($this->toArray($value)) > 0 ? '...' : ''
            );
        }
        if (is_array($value)) {
            return sprintf(
                'Array (%s)',
                count($value) > 0 ? '...' : ''
            );
        }
        return $this->export($value);
    }
    public function toArray($value)
    {
        if (!is_object($value)) {
            return (array) $value;
        }
        $array = [];
        foreach ((array) $value as $key => $val) {
            if (preg_match('/^\0.+\0(.+)$/', $key, $matches)) {
                $key = $matches[1];
            }
            if ($key === "\0gcdata") {
                continue;
            }
            $array[$key] = $val;
        }
        if ($value instanceof \SplObjectStorage) {
            if (property_exists('\SplObjectStorage', '__storage')) {
                unset($array['__storage']);
            } elseif (property_exists('\SplObjectStorage', 'storage')) {
                unset($array['storage']);
            }
            if (property_exists('\SplObjectStorage', '__key')) {
                unset($array['__key']);
            }
            foreach ($value as $key => $val) {
                $array[spl_object_hash($val)] = [
                    'obj' => $val,
                    'inf' => $value->getInfo(),
                ];
            }
        }
        return $array;
    }
    protected function recursiveExport(&$value, $indentation, $processed = null)
    {
        if ($value === null) {
            return 'null';
        }
        if ($value === true) {
            return 'true';
        }
        if ($value === false) {
            return 'false';
        }
        if (is_float($value) && floatval(intval($value)) === $value) {
            return "$value.0";
        }
        if (is_resource($value)) {
            return sprintf(
                'resource(%d) of type (%s)',
                $value,
                get_resource_type($value)
            );
        }
        if (is_string($value)) {
            if (preg_match('/[^\x09-\x0d\x1b\x20-\xff]/', $value)) {
                return 'Binary String: 0x' . bin2hex($value);
            }
            return "'" .
            str_replace('<lf>', "\n",
                str_replace(
                    ["\r\n", "\n\r", "\r", "\n"],
                    ['\r\n<lf>', '\n\r<lf>', '\r<lf>', '\n<lf>'],
                    $value
                )
            ) .
            "'";
        }
        $whitespace = str_repeat(' ', 4 * $indentation);
        if (!$processed) {
            $processed = new Context;
        }
        if (is_array($value)) {
            if (($key = $processed->contains($value)) !== false) {
                return 'Array &' . $key;
            }
            $array  = $value;
            $key    = $processed->add($value);
            $values = '';
            if (count($array) > 0) {
                foreach ($array as $k => $v) {
                    $values .= sprintf(
                        '%s    %s => %s' . "\n",
                        $whitespace,
                        $this->recursiveExport($k, $indentation),
                        $this->recursiveExport($value[$k], $indentation + 1, $processed)
                    );
                }
                $values = "\n" . $values . $whitespace;
            }
            return sprintf('Array &%s (%s)', $key, $values);
        }
        if (is_object($value)) {
            $class = get_class($value);
            if ($hash = $processed->contains($value)) {
                return sprintf('%s Object &%s', $class, $hash);
            }
            $hash   = $processed->add($value);
            $values = '';
            $array  = $this->toArray($value);
            if (count($array) > 0) {
                foreach ($array as $k => $v) {
                    $values .= sprintf(
                        '%s    %s => %s' . "\n",
                        $whitespace,
                        $this->recursiveExport($k, $indentation),
                        $this->recursiveExport($v, $indentation + 1, $processed)
                    );
                }
                $values = "\n" . $values . $whitespace;
            }
            return sprintf('%s Object &%s (%s)', $class, $hash, $values);
        }
        return var_export($value, true);
    }
}
