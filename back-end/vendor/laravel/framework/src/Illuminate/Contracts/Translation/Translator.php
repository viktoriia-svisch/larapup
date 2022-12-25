<?php
namespace Illuminate\Contracts\Translation;
interface Translator
{
    public function trans($key, array $replace = [], $locale = null);
    public function transChoice($key, $number, array $replace = [], $locale = null);
    public function getLocale();
    public function setLocale($locale);
}
