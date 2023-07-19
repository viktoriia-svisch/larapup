<?php
namespace Prophecy\Util;
use Prophecy\Call\Call;
class StringUtil
{
    private $verbose;
    public function __construct($verbose = true)
    {
        $this->verbose = $verbose;
    }
    public function stringify($value, $exportObject = true)
    {
        if (is_array($value)) {
            if (range(0, count($value) - 1) === array_keys($value)) {
                return '['.implode(', ', array_map(array($this, __FUNCTION__), $value)).']';
            }
            $stringify = array($this, __FUNCTION__);
            return '['.implode(', ', array_map(function ($item, $key) use ($stringify) {
                return (is_integer($key) ? $key : '"'.$key.'"').
                    ' => '.call_user_func($stringify, $item);
            }, $value, array_keys($value))).']';
        }
        if (is_resource($value)) {
            return get_resource_type($value).':'.$value;
        }
        if (is_object($value)) {
            return $exportObject ? ExportUtil::export($value) : sprintf('%s:%s', get_class($value), spl_object_hash($value));
        }
        if (true === $value || false === $value) {
            return $value ? 'true' : 'false';
        }
        if (is_string($value)) {
            $str = sprintf('"%s"', str_replace("\n", '\\n', $value));
            if (!$this->verbose && 50 <= strlen($str)) {
                return substr($str, 0, 50).'"...';
            }
            return $str;
        }
        if (null === $value) {
            return 'null';
        }
        return (string) $value;
    }
    public function stringifyCalls(array $calls)
    {
        $self = $this;
        return implode(PHP_EOL, array_map(function (Call $call) use ($self) {
            return sprintf('  - %s(%s) @ %s',
                $call->getMethodName(),
                implode(', ', array_map(array($self, 'stringify'), $call->getArguments())),
                str_replace(GETCWD().DIRECTORY_SEPARATOR, '', $call->getCallPlace())
            );
        }, $calls));
    }
}