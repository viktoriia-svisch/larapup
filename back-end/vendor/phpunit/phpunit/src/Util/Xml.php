<?php
namespace PHPUnit\Util;
use DOMCharacterData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;
use PHPUnit\Framework\Exception;
use ReflectionClass;
final class Xml
{
    public static function import(DOMElement $element): DOMElement
    {
        $document = new DOMDocument;
        return $document->importNode($element, true);
    }
    public static function load($actual, bool $isHtml = false, string $filename = '', bool $xinclude = false, bool $strict = false): DOMDocument
    {
        if ($actual instanceof DOMDocument) {
            return $actual;
        }
        if (!\is_string($actual)) {
            throw new Exception('Could not load XML from ' . \gettype($actual));
        }
        if ($actual === '') {
            throw new Exception('Could not load XML from empty string');
        }
        if ($xinclude) {
            $cwd = \getcwd();
            @\chdir(\dirname($filename));
        }
        $document                     = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $internal  = \libxml_use_internal_errors(true);
        $message   = '';
        $reporting = \error_reporting(0);
        if ($filename !== '') {
            $document->documentURI = $filename;
        }
        if ($isHtml) {
            $loaded = $document->loadHTML($actual);
        } else {
            $loaded = $document->loadXML($actual);
        }
        if (!$isHtml && $xinclude) {
            $document->xinclude();
        }
        foreach (\libxml_get_errors() as $error) {
            $message .= "\n" . $error->message;
        }
        \libxml_use_internal_errors($internal);
        \error_reporting($reporting);
        if (isset($cwd)) {
            @\chdir($cwd);
        }
        if ($loaded === false || ($strict && $message !== '')) {
            if ($filename !== '') {
                throw new Exception(
                    \sprintf(
                        'Could not load "%s".%s',
                        $filename,
                        $message !== '' ? "\n" . $message : ''
                    )
                );
            }
            if ($message === '') {
                $message = 'Could not load XML for unknown reason';
            }
            throw new Exception($message);
        }
        return $document;
    }
    public static function loadFile(string $filename, bool $isHtml = false, bool $xinclude = false, bool $strict = false): DOMDocument
    {
        $reporting = \error_reporting(0);
        $contents  = \file_get_contents($filename);
        \error_reporting($reporting);
        if ($contents === false) {
            throw new Exception(
                \sprintf(
                    'Could not read "%s".',
                    $filename
                )
            );
        }
        return self::load($contents, $isHtml, $filename, $xinclude, $strict);
    }
    public static function removeCharacterDataNodes(DOMNode $node): void
    {
        if ($node->hasChildNodes()) {
            for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
                if (($child = $node->childNodes->item($i)) instanceof DOMCharacterData) {
                    $node->removeChild($child);
                }
            }
        }
    }
    public static function prepareString(string $string): string
    {
        return \preg_replace(
            '/[\\x00-\\x08\\x0b\\x0c\\x0e-\\x1f\\x7f]/',
            '',
            \htmlspecialchars(
                self::convertToUtf8($string),
                \ENT_QUOTES
            )
        );
    }
    public static function xmlToVariable(DOMElement $element)
    {
        $variable = null;
        switch ($element->tagName) {
            case 'array':
                $variable = [];
                foreach ($element->childNodes as $entry) {
                    if (!$entry instanceof DOMElement || $entry->tagName !== 'element') {
                        continue;
                    }
                    $item = $entry->childNodes->item(0);
                    if ($item instanceof DOMText) {
                        $item = $entry->childNodes->item(1);
                    }
                    $value = self::xmlToVariable($item);
                    if ($entry->hasAttribute('key')) {
                        $variable[(string) $entry->getAttribute('key')] = $value;
                    } else {
                        $variable[] = $value;
                    }
                }
                break;
            case 'object':
                $className = $element->getAttribute('class');
                if ($element->hasChildNodes()) {
                    $arguments       = $element->childNodes->item(0)->childNodes;
                    $constructorArgs = [];
                    foreach ($arguments as $argument) {
                        if ($argument instanceof DOMElement) {
                            $constructorArgs[] = self::xmlToVariable($argument);
                        }
                    }
                    $class    = new ReflectionClass($className);
                    $variable = $class->newInstanceArgs($constructorArgs);
                } else {
                    $variable = new $className;
                }
                break;
            case 'boolean':
                $variable = $element->textContent === 'true';
                break;
            case 'integer':
            case 'double':
            case 'string':
                $variable = $element->textContent;
                \settype($variable, $element->tagName);
                break;
        }
        return $variable;
    }
    private static function convertToUtf8(string $string): string
    {
        if (!self::isUtf8($string)) {
            $string = \mb_convert_encoding($string, 'UTF-8');
        }
        return $string;
    }
    private static function isUtf8(string $string): bool
    {
        $length = \strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if (\ord($string[$i]) < 0x80) {
                $n = 0;
            } elseif ((\ord($string[$i]) & 0xE0) === 0xC0) {
                $n = 1;
            } elseif ((\ord($string[$i]) & 0xF0) === 0xE0) {
                $n = 2;
            } elseif ((\ord($string[$i]) & 0xF0) === 0xF0) {
                $n = 3;
            } else {
                return false;
            }
            for ($j = 0; $j < $n; $j++) {
                if ((++$i === $length) || ((\ord($string[$i]) & 0xC0) !== 0x80)) {
                    return false;
                }
            }
        }
        return true;
    }
}
