<?php
namespace Psy\Reflection;
class ReflectionLanguageConstruct extends \ReflectionFunctionAbstract
{
    public $keyword;
    private static $languageConstructs = [
        'isset' => [
            'var' => [],
            '...' => [
                'isOptional'   => true,
                'defaultValue' => null,
            ],
        ],
        'unset' => [
            'var' => [],
            '...' => [
                'isOptional'   => true,
                'defaultValue' => null,
            ],
        ],
        'empty' => [
            'var' => [],
        ],
        'echo' => [
            'arg1' => [],
            '...'  => [
                'isOptional'   => true,
                'defaultValue' => null,
            ],
        ],
        'print' => [
            'arg' => [],
        ],
        'die' => [
            'status' => [
                'isOptional'   => true,
                'defaultValue' => 0,
            ],
        ],
        'exit' => [
            'status' => [
                'isOptional'   => true,
                'defaultValue' => 0,
            ],
        ],
    ];
    public function __construct($keyword)
    {
        if (!self::isLanguageConstruct($keyword)) {
            throw new \InvalidArgumentException('Unknown language construct: ' . $keyword);
        }
        $this->keyword = $keyword;
    }
    public static function export($name)
    {
        throw new \RuntimeException('Not yet implemented because it\'s unclear what I should do here :)');
    }
    public function getName()
    {
        return $this->keyword;
    }
    public function returnsReference()
    {
        return false;
    }
    public function getParameters()
    {
        $params = [];
        foreach (self::$languageConstructs[$this->keyword] as $parameter => $opts) {
            \array_push($params, new ReflectionLanguageConstructParameter($this->keyword, $parameter, $opts));
        }
        return $params;
    }
    public function getFileName()
    {
        return false;
    }
    public function __toString()
    {
        return $this->getName();
    }
    public static function isLanguageConstruct($keyword)
    {
        return \array_key_exists($keyword, self::$languageConstructs);
    }
}
