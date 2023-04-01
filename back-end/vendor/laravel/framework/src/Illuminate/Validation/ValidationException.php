<?php
namespace Illuminate\Validation;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
class ValidationException extends Exception
{
    public $validator;
    public $response;
    public $status = 422;
    public $errorBag;
    public $redirectTo;
    public function __construct($validator, $response = null, $errorBag = 'default')
    {
        parent::__construct('The given data was invalid.');
        $this->response = $response;
        $this->errorBag = $errorBag;
        $this->validator = $validator;
    }
    public static function withMessages(array $messages)
    {
        return new static(tap(ValidatorFacade::make([], []), function ($validator) use ($messages) {
            foreach ($messages as $key => $value) {
                foreach (Arr::wrap($value) as $message) {
                    $validator->errors()->add($key, $message);
                }
            }
        }));
    }
    public function errors()
    {
        return $this->validator->errors()->messages();
    }
    public function status($status)
    {
        $this->status = $status;
        return $this;
    }
    public function errorBag($errorBag)
    {
        $this->errorBag = $errorBag;
        return $this;
    }
    public function redirectTo($url)
    {
        $this->redirectTo = $url;
        return $this;
    }
    public function getResponse()
    {
        return $this->response;
    }
}
