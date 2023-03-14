<?php
namespace Illuminate\Validation;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Contracts\Validation\Rule as RuleContract;
class ValidationRuleParser
{
    public $data;
    public $implicitAttributes = [];
    public function __construct(array $data)
    {
        $this->data = $data;
    }
    public function explode($rules)
    {
        $this->implicitAttributes = [];
        $rules = $this->explodeRules($rules);
        return (object) [
            'rules' => $rules,
            'implicitAttributes' => $this->implicitAttributes,
        ];
    }
    protected function explodeRules($rules)
    {
        foreach ($rules as $key => $rule) {
            if (Str::contains($key, '*')) {
                $rules = $this->explodeWildcardRules($rules, $key, [$rule]);
                unset($rules[$key]);
            } else {
                $rules[$key] = $this->explodeExplicitRule($rule);
            }
        }
        return $rules;
    }
    protected function explodeExplicitRule($rule)
    {
        if (is_string($rule)) {
            return explode('|', $rule);
        } elseif (is_object($rule)) {
            return [$this->prepareRule($rule)];
        }
        return array_map([$this, 'prepareRule'], $rule);
    }
    protected function prepareRule($rule)
    {
        if ($rule instanceof Closure) {
            $rule = new ClosureValidationRule($rule);
        }
        if (! is_object($rule) ||
            $rule instanceof RuleContract ||
            ($rule instanceof Exists && $rule->queryCallbacks()) ||
            ($rule instanceof Unique && $rule->queryCallbacks())) {
            return $rule;
        }
        return (string) $rule;
    }
    protected function explodeWildcardRules($results, $attribute, $rules)
    {
        $pattern = str_replace('\*', '[^\.]*', preg_quote($attribute));
        $data = ValidationData::initializeAndGatherData($attribute, $this->data);
        foreach ($data as $key => $value) {
            if (Str::startsWith($key, $attribute) || (bool) preg_match('/^'.$pattern.'\z/', $key)) {
                foreach ((array) $rules as $rule) {
                    $this->implicitAttributes[$attribute][] = $key;
                    $results = $this->mergeRules($results, $key, $rule);
                }
            }
        }
        return $results;
    }
    public function mergeRules($results, $attribute, $rules = [])
    {
        if (is_array($attribute)) {
            foreach ((array) $attribute as $innerAttribute => $innerRules) {
                $results = $this->mergeRulesForAttribute($results, $innerAttribute, $innerRules);
            }
            return $results;
        }
        return $this->mergeRulesForAttribute(
            $results, $attribute, $rules
        );
    }
    protected function mergeRulesForAttribute($results, $attribute, $rules)
    {
        $merge = head($this->explodeRules([$rules]));
        $results[$attribute] = array_merge(
            isset($results[$attribute]) ? $this->explodeExplicitRule($results[$attribute]) : [], $merge
        );
        return $results;
    }
    public static function parse($rules)
    {
        if ($rules instanceof RuleContract) {
            return [$rules, []];
        }
        if (is_array($rules)) {
            $rules = static::parseArrayRule($rules);
        } else {
            $rules = static::parseStringRule($rules);
        }
        $rules[0] = static::normalizeRule($rules[0]);
        return $rules;
    }
    protected static function parseArrayRule(array $rules)
    {
        return [Str::studly(trim(Arr::get($rules, 0))), array_slice($rules, 1)];
    }
    protected static function parseStringRule($rules)
    {
        $parameters = [];
        if (strpos($rules, ':') !== false) {
            [$rules, $parameter] = explode(':', $rules, 2);
            $parameters = static::parseParameters($rules, $parameter);
        }
        return [Str::studly(trim($rules)), $parameters];
    }
    protected static function parseParameters($rule, $parameter)
    {
        $rule = strtolower($rule);
        if (in_array($rule, ['regex', 'not_regex', 'notregex'], true)) {
            return [$parameter];
        }
        return str_getcsv($parameter);
    }
    protected static function normalizeRule($rule)
    {
        switch ($rule) {
            case 'Int':
                return 'Integer';
            case 'Bool':
                return 'Boolean';
            default:
                return $rule;
        }
    }
}
