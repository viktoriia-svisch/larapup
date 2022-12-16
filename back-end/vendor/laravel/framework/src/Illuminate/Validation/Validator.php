<?php
namespace Illuminate\Validation;
use RuntimeException;
use BadMethodCallException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Fluent;
use Illuminate\Support\MessageBag;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Contracts\Validation\ImplicitRule;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Contracts\Validation\Rule as RuleContract;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
class Validator implements ValidatorContract
{
    use Concerns\FormatsMessages,
        Concerns\ValidatesAttributes;
    protected $translator;
    protected $container;
    protected $presenceVerifier;
    protected $failedRules = [];
    protected $messages;
    protected $data;
    protected $initialRules;
    protected $rules;
    protected $currentRule;
    protected $implicitAttributes = [];
    protected $distinctValues = [];
    protected $after = [];
    public $customMessages = [];
    public $fallbackMessages = [];
    public $customAttributes = [];
    public $customValues = [];
    public $extensions = [];
    public $replacers = [];
    protected $fileRules = [
        'File', 'Image', 'Mimes', 'Mimetypes', 'Min',
        'Max', 'Size', 'Between', 'Dimensions',
    ];
    protected $implicitRules = [
        'Required', 'Filled', 'RequiredWith', 'RequiredWithAll', 'RequiredWithout',
        'RequiredWithoutAll', 'RequiredIf', 'RequiredUnless', 'Accepted', 'Present',
    ];
    protected $dependentRules = [
        'RequiredWith', 'RequiredWithAll', 'RequiredWithout', 'RequiredWithoutAll',
        'RequiredIf', 'RequiredUnless', 'Confirmed', 'Same', 'Different', 'Unique',
        'Before', 'After', 'BeforeOrEqual', 'AfterOrEqual', 'Gt', 'Lt', 'Gte', 'Lte',
    ];
    protected $sizeRules = ['Size', 'Between', 'Min', 'Max', 'Gt', 'Lt', 'Gte', 'Lte'];
    protected $numericRules = ['Numeric', 'Integer'];
    public function __construct(Translator $translator, array $data, array $rules,
                                array $messages = [], array $customAttributes = [])
    {
        $this->initialRules = $rules;
        $this->translator = $translator;
        $this->customMessages = $messages;
        $this->data = $this->parseData($data);
        $this->customAttributes = $customAttributes;
        $this->setRules($rules);
    }
    public function parseData(array $data)
    {
        $newData = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $this->parseData($value);
            }
            if (Str::contains($key, '.')) {
                $newData[str_replace('.', '->', $key)] = $value;
            } else {
                $newData[$key] = $value;
            }
        }
        return $newData;
    }
    public function after($callback)
    {
        $this->after[] = function () use ($callback) {
            return call_user_func_array($callback, [$this]);
        };
        return $this;
    }
    public function passes()
    {
        $this->messages = new MessageBag;
        $this->distinctValues = [];
        foreach ($this->rules as $attribute => $rules) {
            $attribute = str_replace('\.', '->', $attribute);
            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);
                if ($this->shouldStopValidating($attribute)) {
                    break;
                }
            }
        }
        foreach ($this->after as $after) {
            call_user_func($after);
        }
        return $this->messages->isEmpty();
    }
    public function fails()
    {
        return ! $this->passes();
    }
    public function validate()
    {
        if ($this->fails()) {
            throw new ValidationException($this);
        }
        return $this->validated();
    }
    public function validated()
    {
        if ($this->invalid()) {
            throw new ValidationException($this);
        }
        $results = [];
        $missingValue = Str::random(10);
        foreach (array_keys($this->getRules()) as $key) {
            $value = data_get($this->getData(), $key, $missingValue);
            if ($value !== $missingValue) {
                Arr::set($results, $key, $value);
            }
        }
        return $results;
    }
    protected function validateAttribute($attribute, $rule)
    {
        $this->currentRule = $rule;
        [$rule, $parameters] = ValidationRuleParser::parse($rule);
        if ($rule == '') {
            return;
        }
        if (($keys = $this->getExplicitKeys($attribute)) &&
            $this->dependsOnOtherFields($rule)) {
            $parameters = $this->replaceAsterisksInParameters($parameters, $keys);
        }
        $value = $this->getValue($attribute);
        if ($value instanceof UploadedFile && ! $value->isValid() &&
            $this->hasRule($attribute, array_merge($this->fileRules, $this->implicitRules))
        ) {
            return $this->addFailure($attribute, 'uploaded', []);
        }
        $validatable = $this->isValidatable($rule, $attribute, $value);
        if ($rule instanceof RuleContract) {
            return $validatable
                    ? $this->validateUsingCustomRule($attribute, $value, $rule)
                    : null;
        }
        $method = "validate{$rule}";
        if ($validatable && ! $this->$method($attribute, $value, $parameters, $this)) {
            $this->addFailure($attribute, $rule, $parameters);
        }
    }
    protected function dependsOnOtherFields($rule)
    {
        return in_array($rule, $this->dependentRules);
    }
    protected function getExplicitKeys($attribute)
    {
        $pattern = str_replace('\*', '([^\.]+)', preg_quote($this->getPrimaryAttribute($attribute), '/'));
        if (preg_match('/^'.$pattern.'/', $attribute, $keys)) {
            array_shift($keys);
            return $keys;
        }
        return [];
    }
    protected function getPrimaryAttribute($attribute)
    {
        foreach ($this->implicitAttributes as $unparsed => $parsed) {
            if (in_array($attribute, $parsed)) {
                return $unparsed;
            }
        }
        return $attribute;
    }
    protected function replaceAsterisksInParameters(array $parameters, array $keys)
    {
        return array_map(function ($field) use ($keys) {
            return vsprintf(str_replace('*', '%s', $field), $keys);
        }, $parameters);
    }
    protected function isValidatable($rule, $attribute, $value)
    {
        return $this->presentOrRuleIsImplicit($rule, $attribute, $value) &&
               $this->passesOptionalCheck($attribute) &&
               $this->isNotNullIfMarkedAsNullable($rule, $attribute) &&
               $this->hasNotFailedPreviousRuleIfPresenceRule($rule, $attribute);
    }
    protected function presentOrRuleIsImplicit($rule, $attribute, $value)
    {
        if (is_string($value) && trim($value) === '') {
            return $this->isImplicit($rule);
        }
        return $this->validatePresent($attribute, $value) ||
               $this->isImplicit($rule);
    }
    protected function isImplicit($rule)
    {
        return $rule instanceof ImplicitRule ||
               in_array($rule, $this->implicitRules);
    }
    protected function passesOptionalCheck($attribute)
    {
        if (! $this->hasRule($attribute, ['Sometimes'])) {
            return true;
        }
        $data = ValidationData::initializeAndGatherData($attribute, $this->data);
        return array_key_exists($attribute, $data)
            || array_key_exists($attribute, $this->data);
    }
    protected function isNotNullIfMarkedAsNullable($rule, $attribute)
    {
        if ($this->isImplicit($rule) || ! $this->hasRule($attribute, ['Nullable'])) {
            return true;
        }
        return ! is_null(Arr::get($this->data, $attribute, 0));
    }
    protected function hasNotFailedPreviousRuleIfPresenceRule($rule, $attribute)
    {
        return in_array($rule, ['Unique', 'Exists']) ? ! $this->messages->has($attribute) : true;
    }
    protected function validateUsingCustomRule($attribute, $value, $rule)
    {
        if (! $rule->passes($attribute, $value)) {
            $this->failedRules[$attribute][get_class($rule)] = [];
            $messages = (array) $rule->message();
            foreach ($messages as $message) {
                $this->messages->add($attribute, $this->makeReplacements(
                    $message, $attribute, get_class($rule), []
                ));
            }
        }
    }
    protected function shouldStopValidating($attribute)
    {
        if ($this->hasRule($attribute, ['Bail'])) {
            return $this->messages->has($attribute);
        }
        if (isset($this->failedRules[$attribute]) &&
            array_key_exists('uploaded', $this->failedRules[$attribute])) {
            return true;
        }
        return $this->hasRule($attribute, $this->implicitRules) &&
               isset($this->failedRules[$attribute]) &&
               array_intersect(array_keys($this->failedRules[$attribute]), $this->implicitRules);
    }
    public function addFailure($attribute, $rule, $parameters = [])
    {
        if (! $this->messages) {
            $this->passes();
        }
        $this->messages->add($attribute, $this->makeReplacements(
            $this->getMessage($attribute, $rule), $attribute, $rule, $parameters
        ));
        $this->failedRules[$attribute][$rule] = $parameters;
    }
    public function valid()
    {
        if (! $this->messages) {
            $this->passes();
        }
        return array_diff_key(
            $this->data, $this->attributesThatHaveMessages()
        );
    }
    public function invalid()
    {
        if (! $this->messages) {
            $this->passes();
        }
        return array_intersect_key(
            $this->data, $this->attributesThatHaveMessages()
        );
    }
    protected function attributesThatHaveMessages()
    {
        return collect($this->messages()->toArray())->map(function ($message, $key) {
            return explode('.', $key)[0];
        })->unique()->flip()->all();
    }
    public function failed()
    {
        return $this->failedRules;
    }
    public function messages()
    {
        if (! $this->messages) {
            $this->passes();
        }
        return $this->messages;
    }
    public function errors()
    {
        return $this->messages();
    }
    public function getMessageBag()
    {
        return $this->messages();
    }
    public function hasRule($attribute, $rules)
    {
        return ! is_null($this->getRule($attribute, $rules));
    }
    protected function getRule($attribute, $rules)
    {
        if (! array_key_exists($attribute, $this->rules)) {
            return;
        }
        $rules = (array) $rules;
        foreach ($this->rules[$attribute] as $rule) {
            [$rule, $parameters] = ValidationRuleParser::parse($rule);
            if (in_array($rule, $rules)) {
                return [$rule, $parameters];
            }
        }
    }
    public function attributes()
    {
        return $this->getData();
    }
    public function getData()
    {
        return $this->data;
    }
    public function setData(array $data)
    {
        $this->data = $this->parseData($data);
        $this->setRules($this->initialRules);
        return $this;
    }
    protected function getValue($attribute)
    {
        return Arr::get($this->data, $attribute);
    }
    public function getRules()
    {
        return $this->rules;
    }
    public function setRules(array $rules)
    {
        $this->initialRules = $rules;
        $this->rules = [];
        $this->addRules($rules);
        return $this;
    }
    public function addRules($rules)
    {
        $response = (new ValidationRuleParser($this->data))
                            ->explode($rules);
        $this->rules = array_merge_recursive(
            $this->rules, $response->rules
        );
        $this->implicitAttributes = array_merge(
            $this->implicitAttributes, $response->implicitAttributes
        );
    }
    public function sometimes($attribute, $rules, callable $callback)
    {
        $payload = new Fluent($this->getData());
        if (call_user_func($callback, $payload)) {
            foreach ((array) $attribute as $key) {
                $this->addRules([$key => $rules]);
            }
        }
        return $this;
    }
    public function addExtensions(array $extensions)
    {
        if ($extensions) {
            $keys = array_map('\Illuminate\Support\Str::snake', array_keys($extensions));
            $extensions = array_combine($keys, array_values($extensions));
        }
        $this->extensions = array_merge($this->extensions, $extensions);
    }
    public function addImplicitExtensions(array $extensions)
    {
        $this->addExtensions($extensions);
        foreach ($extensions as $rule => $extension) {
            $this->implicitRules[] = Str::studly($rule);
        }
    }
    public function addDependentExtensions(array $extensions)
    {
        $this->addExtensions($extensions);
        foreach ($extensions as $rule => $extension) {
            $this->dependentRules[] = Str::studly($rule);
        }
    }
    public function addExtension($rule, $extension)
    {
        $this->extensions[Str::snake($rule)] = $extension;
    }
    public function addImplicitExtension($rule, $extension)
    {
        $this->addExtension($rule, $extension);
        $this->implicitRules[] = Str::studly($rule);
    }
    public function addDependentExtension($rule, $extension)
    {
        $this->addExtension($rule, $extension);
        $this->dependentRules[] = Str::studly($rule);
    }
    public function addReplacers(array $replacers)
    {
        if ($replacers) {
            $keys = array_map('\Illuminate\Support\Str::snake', array_keys($replacers));
            $replacers = array_combine($keys, array_values($replacers));
        }
        $this->replacers = array_merge($this->replacers, $replacers);
    }
    public function addReplacer($rule, $replacer)
    {
        $this->replacers[Str::snake($rule)] = $replacer;
    }
    public function setCustomMessages(array $messages)
    {
        $this->customMessages = array_merge($this->customMessages, $messages);
        return $this;
    }
    public function setAttributeNames(array $attributes)
    {
        $this->customAttributes = $attributes;
        return $this;
    }
    public function addCustomAttributes(array $customAttributes)
    {
        $this->customAttributes = array_merge($this->customAttributes, $customAttributes);
        return $this;
    }
    public function setValueNames(array $values)
    {
        $this->customValues = $values;
        return $this;
    }
    public function addCustomValues(array $customValues)
    {
        $this->customValues = array_merge($this->customValues, $customValues);
        return $this;
    }
    public function setFallbackMessages(array $messages)
    {
        $this->fallbackMessages = $messages;
    }
    public function getPresenceVerifier()
    {
        if (! isset($this->presenceVerifier)) {
            throw new RuntimeException('Presence verifier has not been set.');
        }
        return $this->presenceVerifier;
    }
    protected function getPresenceVerifierFor($connection)
    {
        return tap($this->getPresenceVerifier(), function ($verifier) use ($connection) {
            $verifier->setConnection($connection);
        });
    }
    public function setPresenceVerifier(PresenceVerifierInterface $presenceVerifier)
    {
        $this->presenceVerifier = $presenceVerifier;
    }
    public function getTranslator()
    {
        return $this->translator;
    }
    public function setTranslator(Translator $translator)
    {
        $this->translator = $translator;
    }
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
    protected function callExtension($rule, $parameters)
    {
        $callback = $this->extensions[$rule];
        if (is_callable($callback)) {
            return call_user_func_array($callback, $parameters);
        } elseif (is_string($callback)) {
            return $this->callClassBasedExtension($callback, $parameters);
        }
    }
    protected function callClassBasedExtension($callback, $parameters)
    {
        [$class, $method] = Str::parseCallback($callback, 'validate');
        return call_user_func_array([$this->container->make($class), $method], $parameters);
    }
    public function __call($method, $parameters)
    {
        $rule = Str::snake(substr($method, 8));
        if (isset($this->extensions[$rule])) {
            return $this->callExtension($rule, $parameters);
        }
        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
