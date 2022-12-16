<?php
namespace Illuminate\Validation\Rules;
class NotIn
{
    protected $rule = 'not_in';
    protected $values;
    public function __construct(array $values)
    {
        $this->values = $values;
    }
    public function __toString()
    {
        $values = array_map(function ($value) {
            return '"'.str_replace('"', '""', $value).'"';
        }, $this->values);
        return $this->rule.':'.implode(',', $values);
    }
}
