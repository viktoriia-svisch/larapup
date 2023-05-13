<?php
namespace Illuminate\View;
class ViewName
{
    public static function normalize($name)
    {
        $delimiter = ViewFinderInterface::HINT_PATH_DELIMITER;
        if (strpos($name, $delimiter) === false) {
            return str_replace('/', '.', $name);
        }
        [$namespace, $name] = explode($delimiter, $name);
        return $namespace.$delimiter.str_replace('/', '.', $name);
    }
}
