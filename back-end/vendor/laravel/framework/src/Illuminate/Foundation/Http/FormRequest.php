<?php
namespace Illuminate\Foundation\Http;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidatesWhenResolvedTrait;
use Illuminate\Contracts\Validation\ValidatesWhenResolved;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
class FormRequest extends Request implements ValidatesWhenResolved
{
    use ValidatesWhenResolvedTrait;
    protected $container;
    protected $redirector;
    protected $redirect;
    protected $redirectRoute;
    protected $redirectAction;
    protected $errorBag = 'default';
    protected $validator;
    protected function getValidatorInstance()
    {
        $factory = $this->container->make(ValidationFactory::class);
        if (method_exists($this, 'validator')) {
            $validator = $this->container->call([$this, 'validator'], compact('factory'));
        } else {
            $validator = $this->createDefaultValidator($factory);
        }
        if (method_exists($this, 'withValidator')) {
            $this->withValidator($validator);
        }
        $this->setValidator($validator);
        return $this->validator;
    }
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->validationData(), $this->container->call([$this, 'rules']),
            $this->messages(), $this->attributes()
        );
    }
    protected function validationData()
    {
        return $this->all();
    }
    protected function failedValidation(Validator $validator)
    {
        throw (new ValidationException($validator))
                    ->errorBag($this->errorBag)
                    ->redirectTo($this->getRedirectUrl());
    }
    protected function getRedirectUrl()
    {
        $url = $this->redirector->getUrlGenerator();
        if ($this->redirect) {
            return $url->to($this->redirect);
        } elseif ($this->redirectRoute) {
            return $url->route($this->redirectRoute);
        } elseif ($this->redirectAction) {
            return $url->action($this->redirectAction);
        }
        return $url->previous();
    }
    protected function passesAuthorization()
    {
        if (method_exists($this, 'authorize')) {
            return $this->container->call([$this, 'authorize']);
        }
        return true;
    }
    protected function failedAuthorization()
    {
        throw new AuthorizationException('This action is unauthorized.');
    }
    public function validated()
    {
        return $this->validator->validated();
    }
    public function messages()
    {
        return [];
    }
    public function attributes()
    {
        return [];
    }
    public function setValidator(Validator $validator)
    {
        $this->validator = $validator;
        return $this;
    }
    public function setRedirector(Redirector $redirector)
    {
        $this->redirector = $redirector;
        return $this;
    }
    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }
}
