<?php
namespace TijsVerkoyen\CssToInlineStyles\Css\Property;
use Symfony\Component\CssSelector\Node\Specificity;
class Processor
{
    public function splitIntoSeparateProperties($propertiesString)
    {
        $propertiesString = $this->cleanup($propertiesString);
        $properties = (array) explode(';', $propertiesString);
        $keysToRemove = array();
        $numberOfProperties = count($properties);
        for ($i = 0; $i < $numberOfProperties; $i++) {
            $properties[$i] = trim($properties[$i]);
            if (isset($properties[$i + 1]) && strpos(trim($properties[$i + 1]), 'base64,') === 0) {
                $properties[$i] .= ';' . trim($properties[$i + 1]);
                $keysToRemove[] = $i + 1;
            }
        }
        if (!empty($keysToRemove)) {
            foreach ($keysToRemove as $key) {
                unset($properties[$key]);
            }
        }
        return array_values($properties);
    }
    private function cleanup($string)
    {
        $string = str_replace(array("\r", "\n"), '', $string);
        $string = str_replace(array("\t"), ' ', $string);
        $string = str_replace('"', '\'', $string);
        $string = preg_replace('|/\*.*?\*/|', '', $string);
        $string = preg_replace('/\s\s+/', ' ', $string);
        $string = trim($string);
        $string = rtrim($string, ';');
        return $string;
    }
    public function convertToObject($property, Specificity $specificity = null)
    {
        if (strpos($property, ':') === false) {
            return null;
        }
        list($name, $value) = explode(':', $property, 2);
        $name = trim($name);
        $value = trim($value);
        if ($value === '') {
            return null;
        }
        return new Property($name, $value, $specificity);
    }
    public function convertArrayToObjects(array $properties, Specificity $specificity = null)
    {
        $objects = array();
        foreach ($properties as $property) {
            $object = $this->convertToObject($property, $specificity);
            if ($object === null) {
                continue;
            }
            $objects[] = $object;
        }
        return $objects;
    }
    public function buildPropertiesString(array $properties)
    {
        $chunks = array();
        foreach ($properties as $property) {
            $chunks[] = $property->toString();
        }
        return implode(' ', $chunks);
    }
}
