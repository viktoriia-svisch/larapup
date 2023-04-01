<?php
namespace Illuminate\Contracts\Translation;
interface Loader
{
    public function load($locale, $group, $namespace = null);
    public function addNamespace($namespace, $hint);
    public function addJsonPath($path);
    public function namespaces();
}
