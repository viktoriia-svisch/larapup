<?php declare(strict_types=1);
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class Identifier extends NodeAbstract
{
    public $name;
    private static $specialClassNames = [
        'self'   => true,
        'parent' => true,
        'static' => true,
    ];
    public function __construct(string $name, array $attributes = []) {
        parent::__construct($attributes);
        $this->name = $name;
    }
    public function getSubNodeNames() : array {
        return ['name'];
    }
    public function toString() : string {
        return $this->name;
    }
    public function toLowerString() : string {
        return strtolower($this->name);
    }
    public function isSpecialClassName() : bool {
        return isset(self::$specialClassNames[strtolower($this->name)]);
    }
    public function __toString() : string {
        return $this->name;
    }
    public function getType() : string {
        return 'Identifier';
    }
}
