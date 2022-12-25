<?php
namespace Illuminate\Contracts\Validation;
use Illuminate\Contracts\Support\MessageProvider;
interface Validator extends MessageProvider
{
    public function validate();
    public function fails();
    public function failed();
    public function sometimes($attribute, $rules, callable $callback);
    public function after($callback);
    public function errors();
}
