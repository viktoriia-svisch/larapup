<?php
namespace Illuminate\Translation;
use Countable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\NamespacedItemResolver;
use Illuminate\Contracts\Translation\Translator as TranslatorContract;
class Translator extends NamespacedItemResolver implements TranslatorContract
{
    use Macroable;
    protected $loader;
    protected $locale;
    protected $fallback;
    protected $loaded = [];
    protected $selector;
    public function __construct(Loader $loader, $locale)
    {
        $this->loader = $loader;
        $this->locale = $locale;
    }
    public function hasForLocale($key, $locale = null)
    {
        return $this->has($key, $locale, false);
    }
    public function has($key, $locale = null, $fallback = true)
    {
        return $this->get($key, [], $locale, $fallback) !== $key;
    }
    public function trans($key, array $replace = [], $locale = null)
    {
        return $this->get($key, $replace, $locale);
    }
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        [$namespace, $group, $item] = $this->parseKey($key);
        $locales = $fallback ? $this->localeArray($locale)
                             : [$locale ?: $this->locale];
        foreach ($locales as $locale) {
            if (! is_null($line = $this->getLine(
                $namespace, $group, $locale, $item, $replace
            ))) {
                break;
            }
        }
        if (isset($line)) {
            return $line;
        }
        return $key;
    }
    public function getFromJson($key, array $replace = [], $locale = null)
    {
        $locale = $locale ?: $this->locale;
        $this->load('*', '*', $locale);
        $line = $this->loaded['*']['*'][$locale][$key] ?? null;
        if (! isset($line)) {
            $fallback = $this->get($key, $replace, $locale);
            if ($fallback !== $key) {
                return $fallback;
            }
        }
        return $this->makeReplacements($line ?: $key, $replace);
    }
    public function transChoice($key, $number, array $replace = [], $locale = null)
    {
        return $this->choice($key, $number, $replace, $locale);
    }
    public function choice($key, $number, array $replace = [], $locale = null)
    {
        $line = $this->get(
            $key, $replace, $locale = $this->localeForChoice($locale)
        );
        if (is_array($number) || $number instanceof Countable) {
            $number = count($number);
        }
        $replace['count'] = $number;
        return $this->makeReplacements(
            $this->getSelector()->choose($line, $number, $locale), $replace
        );
    }
    protected function localeForChoice($locale)
    {
        return $locale ?: $this->locale ?: $this->fallback;
    }
    protected function getLine($namespace, $group, $locale, $item, array $replace)
    {
        $this->load($namespace, $group, $locale);
        $line = Arr::get($this->loaded[$namespace][$group][$locale], $item);
        if (is_string($line)) {
            return $this->makeReplacements($line, $replace);
        } elseif (is_array($line) && count($line) > 0) {
            foreach ($line as $key => $value) {
                $line[$key] = $this->makeReplacements($value, $replace);
            }
            return $line;
        }
    }
    protected function makeReplacements($line, array $replace)
    {
        if (empty($replace)) {
            return $line;
        }
        $replace = $this->sortReplacements($replace);
        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':'.$key, ':'.Str::upper($key), ':'.Str::ucfirst($key)],
                [$value, Str::upper($value), Str::ucfirst($value)],
                $line
            );
        }
        return $line;
    }
    protected function sortReplacements(array $replace)
    {
        return (new Collection($replace))->sortBy(function ($value, $key) {
            return mb_strlen($key) * -1;
        })->all();
    }
    public function addLines(array $lines, $locale, $namespace = '*')
    {
        foreach ($lines as $key => $value) {
            [$group, $item] = explode('.', $key, 2);
            Arr::set($this->loaded, "$namespace.$group.$locale.$item", $value);
        }
    }
    public function load($namespace, $group, $locale)
    {
        if ($this->isLoaded($namespace, $group, $locale)) {
            return;
        }
        $lines = $this->loader->load($locale, $group, $namespace);
        $this->loaded[$namespace][$group][$locale] = $lines;
    }
    protected function isLoaded($namespace, $group, $locale)
    {
        return isset($this->loaded[$namespace][$group][$locale]);
    }
    public function addNamespace($namespace, $hint)
    {
        $this->loader->addNamespace($namespace, $hint);
    }
    public function addJsonPath($path)
    {
        $this->loader->addJsonPath($path);
    }
    public function parseKey($key)
    {
        $segments = parent::parseKey($key);
        if (is_null($segments[0])) {
            $segments[0] = '*';
        }
        return $segments;
    }
    protected function localeArray($locale)
    {
        return array_filter([$locale ?: $this->locale, $this->fallback]);
    }
    public function getSelector()
    {
        if (! isset($this->selector)) {
            $this->selector = new MessageSelector;
        }
        return $this->selector;
    }
    public function setSelector(MessageSelector $selector)
    {
        $this->selector = $selector;
    }
    public function getLoader()
    {
        return $this->loader;
    }
    public function locale()
    {
        return $this->getLocale();
    }
    public function getLocale()
    {
        return $this->locale;
    }
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    public function getFallback()
    {
        return $this->fallback;
    }
    public function setFallback($fallback)
    {
        $this->fallback = $fallback;
    }
    public function setLoaded(array $loaded)
    {
        $this->loaded = $loaded;
    }
}
