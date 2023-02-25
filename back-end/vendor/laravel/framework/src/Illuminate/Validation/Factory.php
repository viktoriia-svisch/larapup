<?php
namespace Illuminate\Validation;
use Closure;
use Illuminate\Support\Str;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\Factory as FactoryContract;
class Factory implements FactoryContract
{
    protected $translator;
    protected $verifier;
    protected $container;
    protected $extensions = [];
    protected $implicitExtensions = [];
    protected $dependentExtensions = [];
    protected $replacers = [];
    protected $fallbackMessages = [];
    protected $resolver;
    public function __construct(Translator $translator, Container $container = null)
    {
        $this->container = $container;
        $this->translator = $translator;
    }
    public function make(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        $validator = $this->resolve(
            $data, $rules, $messages, $customAttributes
        );
        if (! is_null($this->verifier)) {
            $validator->setPresenceVerifier($this->verifier);
        }
        if (! is_null($this->container)) {
            $validator->setContainer($this->container);
        }
        $this->addExtensions($validator);
        return $validator;
    }
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return $this->make($data, $rules, $messages, $customAttributes)->validate();
    }
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (is_null($this->resolver)) {
            return new Validator($this->translator, $data, $rules, $messages, $customAttributes);
        }
        return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
    }
    protected function addExtensions(Validator $validator)
    {
        $validator->addExtensions($this->extensions);
        $validator->addImplicitExtensions($this->implicitExtensions);
        $validator->addDependentExtensions($this->dependentExtensions);
        $validator->addReplacers($this->replacers);
        $validator->setFallbackMessages($this->fallbackMessages);
    }
    public function extend($rule, $extension, $message = null)
    {
        $this->extensions[$rule] = $extension;
        if ($message) {
            $this->fallbackMessages[Str::snake($rule)] = $message;
        }
    }
    public function extendImplicit($rule, $extension, $message = null)
    {
        $this->implicitExtensions[$rule] = $extension;
        if ($message) {
            $this->fallbackMessages[Str::snake($rule)] = $message;
        }
    }
    public function extendDependent($rule, $extension, $message = null)
    {
        $this->dependentExtensions[$rule] = $extension;
        if ($message) {
            $this->fallbackMessages[Str::snake($rule)] = $message;
        }
    }
    public function replacer($rule, $replacer)
    {
        $this->replacers[$rule] = $replacer;
    }
    public function resolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }
    public function getTranslator()
    {
        return $this->translator;
    }
    public function getPresenceVerifier()
    {
        return $this->verifier;
    }
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
    {
        $this->verifier = $presenceVerifier;
    }
}
