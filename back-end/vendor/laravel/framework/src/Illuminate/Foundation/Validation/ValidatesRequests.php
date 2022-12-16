<?php
namespace Illuminate\Foundation\Validation;
use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Validation\ValidationException;
trait ValidatesRequests
{
    public function validateWith($validator, Request $request = null)
    {
        $request = $request ?: request();
        if (is_array($validator)) {
            $validator = $this->getValidationFactory()->make($request->all(), $validator);
        }
        return $validator->validate();
    }
    public function validate(Request $request, array $rules,
                             array $messages = [], array $customAttributes = [])
    {
        return $this->getValidationFactory()->make(
            $request->all(), $rules, $messages, $customAttributes
        )->validate();
    }
    public function validateWithBag($errorBag, Request $request, array $rules,
                                    array $messages = [], array $customAttributes = [])
    {
        try {
            return $this->validate($request, $rules, $messages, $customAttributes);
        } catch (ValidationException $e) {
            $e->errorBag = $errorBag;
            throw $e;
        }
    }
    protected function getValidationFactory()
    {
        return app(Factory::class);
    }
}
