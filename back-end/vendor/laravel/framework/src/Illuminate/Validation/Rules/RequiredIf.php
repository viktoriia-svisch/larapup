<?php
namespace Illuminate\Validation\Rules;
class RequiredIf
{
    public $condition;
    public function __construct($condition)
    {
        $this->condition = $condition;
    }
    public function __toString()
    {
        if (is_callable($this->condition)) {
            return call_user_func($this->condition) ? 'required' : '';
        }
        return $this->condition ? 'required' : '';
    }
}
