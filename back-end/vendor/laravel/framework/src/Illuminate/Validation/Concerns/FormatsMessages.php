<?php
namespace Illuminate\Validation\Concerns;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
trait FormatsMessages
{
    use ReplacesAttributes;
    protected function getMessage($attribute, $rule)
    {
        $inlineMessage = $this->getInlineMessage($attribute, $rule);
        if (! is_null($inlineMessage)) {
            return $inlineMessage;
        }
        $lowerRule = Str::snake($rule);
        $customMessage = $this->getCustomMessageFromTranslator(
            $customKey = "validation.custom.{$attribute}.{$lowerRule}"
        );
        if ($customMessage !== $customKey) {
            return $customMessage;
        }
        elseif (in_array($rule, $this->sizeRules)) {
            return $this->getSizeMessage($attribute, $rule);
        }
        $key = "validation.{$lowerRule}";
        if ($key != ($value = $this->translator->trans($key))) {
            return $value;
        }
        return $this->getFromLocalArray(
            $attribute, $lowerRule, $this->fallbackMessages
        ) ?: $key;
    }
    protected function getInlineMessage($attribute, $rule)
    {
        $inlineEntry = $this->getFromLocalArray($attribute, Str::snake($rule));
        return is_array($inlineEntry) && in_array($rule, $this->sizeRules)
                    ? $inlineEntry[$this->getAttributeType($attribute)]
                    : $inlineEntry;
    }
    protected function getFromLocalArray($attribute, $lowerRule, $source = null)
    {
        $source = $source ?: $this->customMessages;
        $keys = ["{$attribute}.{$lowerRule}", $lowerRule];
        foreach ($keys as $key) {
            foreach (array_keys($source) as $sourceKey) {
                if (Str::is($sourceKey, $key)) {
                    return $source[$sourceKey];
                }
            }
        }
    }
    protected function getCustomMessageFromTranslator($key)
    {
        if (($message = $this->translator->trans($key)) !== $key) {
            return $message;
        }
        $shortKey = preg_replace(
            '/^validation\.custom\./', '', $key
        );
        return $this->getWildcardCustomMessages(Arr::dot(
            (array) $this->translator->trans('validation.custom')
        ), $shortKey, $key);
    }
    protected function getWildcardCustomMessages($messages, $search, $default)
    {
        foreach ($messages as $key => $message) {
            if ($search === $key || (Str::contains($key, ['*']) && Str::is($key, $search))) {
                return $message;
            }
        }
        return $default;
    }
    protected function getSizeMessage($attribute, $rule)
    {
        $lowerRule = Str::snake($rule);
        $type = $this->getAttributeType($attribute);
        $key = "validation.{$lowerRule}.{$type}";
        return $this->translator->trans($key);
    }
    protected function getAttributeType($attribute)
    {
        if ($this->hasRule($attribute, $this->numericRules)) {
            return 'numeric';
        } elseif ($this->hasRule($attribute, ['Array'])) {
            return 'array';
        } elseif ($this->getValue($attribute) instanceof UploadedFile) {
            return 'file';
        }
        return 'string';
    }
    public function makeReplacements($message, $attribute, $rule, $parameters)
    {
        $message = $this->replaceAttributePlaceholder(
            $message, $this->getDisplayableAttribute($attribute)
        );
        $message = $this->replaceInputPlaceholder($message, $attribute);
        if (isset($this->replacers[Str::snake($rule)])) {
            return $this->callReplacer($message, $attribute, Str::snake($rule), $parameters, $this);
        } elseif (method_exists($this, $replacer = "replace{$rule}")) {
            return $this->$replacer($message, $attribute, $rule, $parameters);
        }
        return $message;
    }
    public function getDisplayableAttribute($attribute)
    {
        $primaryAttribute = $this->getPrimaryAttribute($attribute);
        $expectedAttributes = $attribute != $primaryAttribute
                    ? [$attribute, $primaryAttribute] : [$attribute];
        foreach ($expectedAttributes as $name) {
            if (isset($this->customAttributes[$name])) {
                return $this->customAttributes[$name];
            }
            if ($line = $this->getAttributeFromTranslations($name)) {
                return $line;
            }
        }
        if (isset($this->implicitAttributes[$primaryAttribute])) {
            return $attribute;
        }
        return str_replace('_', ' ', Str::snake($attribute));
    }
    protected function getAttributeFromTranslations($name)
    {
        return Arr::get($this->translator->trans('validation.attributes'), $name);
    }
    protected function replaceAttributePlaceholder($message, $value)
    {
        return str_replace(
            [':attribute', ':ATTRIBUTE', ':Attribute'],
            [$value, Str::upper($value), Str::ucfirst($value)],
            $message
        );
    }
    protected function replaceInputPlaceholder($message, $attribute)
    {
        $actualValue = $this->getValue($attribute);
        if (is_scalar($actualValue) || is_null($actualValue)) {
            $message = str_replace(':input', $actualValue, $message);
        }
        return $message;
    }
    public function getDisplayableValue($attribute, $value)
    {
        if (isset($this->customValues[$attribute][$value])) {
            return $this->customValues[$attribute][$value];
        }
        $key = "validation.values.{$attribute}.{$value}";
        if (($line = $this->translator->trans($key)) !== $key) {
            return $line;
        }
        return $value;
    }
    protected function getAttributeList(array $values)
    {
        $attributes = [];
        foreach ($values as $key => $value) {
            $attributes[$key] = $this->getDisplayableAttribute($value);
        }
        return $attributes;
    }
    protected function callReplacer($message, $attribute, $rule, $parameters, $validator)
    {
        $callback = $this->replacers[$rule];
        if ($callback instanceof Closure) {
            return call_user_func_array($callback, func_get_args());
        } elseif (is_string($callback)) {
            return $this->callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters, $validator);
        }
    }
    protected function callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters, $validator)
    {
        [$class, $method] = Str::parseCallback($callback, 'replace');
        return call_user_func_array([$this->container->make($class), $method], array_slice(func_get_args(), 1));
    }
}
