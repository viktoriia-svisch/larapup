<?php
namespace Illuminate\Validation;
trait ValidatesWhenResolvedTrait
{
    public function validateResolved()
    {
        $this->prepareForValidation();
        if (! $this->passesAuthorization()) {
            $this->failedAuthorization();
        }
        $instance = $this->getValidatorInstance();
        if ($instance->fails()) {
            $this->failedValidation($instance);
        }
    }
    protected function prepareForValidation()
    {
    }
    protected function getValidatorInstance()
    {
        return $this->validator();
    }
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator);
    }
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->authorize();
        }
        return true;
    }
    protected function failedAuthorization()
    {
        throw new UnauthorizedException;
    }
}
