<?php
namespace Illuminate\Validation;
use Illuminate\Contracts\Validation\Rule as RuleContract;
class ClosureValidationRule implements RuleContract
{
    public $callback;
    public $failed = false;
    public $message;
    public function __construct($callback)
    {
        $this->callback = $callback;
    }
    public function passes($attribute, $value)
    {
        $this->failed = false;
        $this->callback->__invoke($attribute, $value, function ($message) {
            $this->failed = true;
            $this->message = $message;
        });
        return ! $this->failed;
    }
    public function message()
    {
        return $this->message;
    }
}
